<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Salary;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Designation;
use App\Models\Schedule;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $employees = Employee::with(['department', 'designation', 'schedule'])->get();
        return view('admin.employee.index',compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::all();
        return view('admin.employee.create', compact('departments', 'designations', 'schedules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request)
    {
        //
        $data = $request->all();

        // Ensure a valid status: the blank form option resolves to '' which
        // must never reach the NOT NULL column. Default to Currently Employed.
        if (empty($data['status'])) {
            $data['status'] = 1;
        }

        // Auto-generate a unique employee ID if the field was left blank.
        if (empty($data['unique_id'])) {
            $data['unique_id'] = 'EMP-' . strtoupper(uniqid());
        }

        $employee = Employee::create($data);
        if($employee) {
            $salary = new Salary($data);
            $employee->salary()->save($salary);
        }
        return back()->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $employee = Employee::with([
            'department', 'designation', 'schedule',
            'salary', 'leaves', 'payrolls', 'attendances',
            'lateTime', 'overTime',
        ])->findOrFail($id);
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::all();
        return view('admin.employee.show', compact('employee', 'departments', 'designations', 'schedules'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::all();
        return view('admin.employee.edit',compact('employee', 'departments', 'designations', 'schedules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        //
        $data = $request->all();

        if (empty($data['status'])) {
            $data['status'] = 1;
        }

        $employee->update($data);
        $salary = $employee->salary ?: new Salary();
        $salary->fill($data);
        $employee->salary()->save($salary);

        // return Redirect::route('admin.employee.edit')->with('success', 'Employee updated successfully');
        return back()->with('success', 'Employee updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        //
        $employee->delete();
        return back()->with('success','Employee deleted successfully');
    }

    /**
     * AJAX autocomplete: search employees by Employee ID or Name.
     */
    public function search(Request $request)
    {
        $q = trim($request->input('query', ''));
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $employees = Employee::with(['department', 'designation'])
            ->where('unique_id', 'like', "%{$q}%")
            ->orWhere('firstname', 'like', "%{$q}%")
            ->orWhere('lastname', 'like', "%{$q}%")
            ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$q}%"])
            ->limit(10)
            ->get()
            ->map(fn($emp) => [
                'id'          => $emp->id,
                'unique_id'   => $emp->unique_id,
                'name'        => $emp->firstname . ' ' . $emp->lastname,
                'department'  => optional($emp->department)->title ?? '-',
                'designation' => optional($emp->designation)->title ?? '-',
                'status'      => $emp->status,
            ]);

        return response()->json($employees);
    }

    /**
     * Employee Search & Profile page.
     */
    public function searchPage(Request $request)
    {
        $query    = trim($request->input('query', ''));
        $employee = null;
        $results  = collect();

        if ($query) {
            // Try exact unique_id match first
            $employee = Employee::with([
                'department', 'designation', 'schedule',
                'salary', 'leaves', 'payrolls', 'attendances',
                'lateTime', 'overTime',
            ])->where('unique_id', $query)->first();

            // If not found by ID, do a broader name search
            if (!$employee) {
                $results = Employee::with(['department', 'designation'])
                    ->where('unique_id', 'like', "%{$query}%")
                    ->orWhere('firstname', 'like', "%{$query}%")
                    ->orWhere('lastname', 'like', "%{$query}%")
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$query}%"])
                    ->get();

                // Auto-select if only one result
                if ($results->count() === 1) {
                    $employee = Employee::with([
                        'department', 'designation', 'schedule',
                        'salary', 'leaves', 'payrolls', 'attendances',
                        'lateTime', 'overTime',
                    ])->find($results->first()->id);
                    $results = collect();
                }
            }
        }

        return view('admin.employee.search', compact('query', 'employee', 'results'));
    }
}
