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
        $data = $request->all();

        // Ensure a valid status: default to Currently Employed
        if (empty($data['status'])) {
            $data['status'] = 1;
        }

        // Auto-generate a unique employee ID if blank
        if (empty($data['unique_id'])) {
            $data['unique_id'] = 'EMP-' . strtoupper(uniqid());
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads/avatars'), $filename);
            $data['avatar'] = 'uploads/avatars/' . $filename;
        }

        $employee = Employee::create($data);
        if ($employee) {
            $salary = new Salary($data);
            $employee->salary()->save($salary);

            \App\Models\EmployeeHistory::log(
                $employee->id,
                'status_change',
                'Employee Onboarded',
                "Employee record created for {$employee->firstname} {$employee->lastname} with ID {$employee->unique_id}."
            );

            // Auto-initialize standard leave balances for current year if leave types exist
            try {
                $leaveTypes = \App\Models\LeaveType::where('status', true)->get();
                $currentYear = (int) date('Y');
                foreach ($leaveTypes as $lt) {
                    \App\Models\EmployeeLeaveBalance::firstOrCreate([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $lt->id,
                        'year' => $currentYear,
                    ], [
                        'total_allocated' => $lt->annual_quota_days,
                        'used_days' => 0,
                        'pending_days' => 0,
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore if tables not yet populated
            }
        }
        return back()->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employee = Employee::with([
            'department', 'designation', 'schedule',
            'salary', 'leaves', 'payrolls', 'attendances',
            'departs', 'lateTime', 'overTime',
            'shiftRosters.schedule', 'histories',
            'documents', 'leaveBalances.leaveType',
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
        $employee = Employee::findOrFail($id);
        $departments = Department::all();
        $designations = Designation::all();
        $schedules = Schedule::all();
        return view('admin.employee.edit',compact('employee', 'departments', 'designations', 'schedules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, $employee)
    {
        $targetEmployee = $employee instanceof Employee && $employee->exists
            ? $employee
            : Employee::findOrFail($request->route('id') ?? (is_numeric($employee) ? $employee : $employee->id));

        $data = $request->all();

        if (empty($data['status'])) {
            $data['status'] = 1;
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $avatar->move(public_path('uploads/avatars'), $filename);
            $data['avatar'] = 'uploads/avatars/' . $filename;
        }

        $oldDept = $targetEmployee->department_id;
        $oldDesig = $targetEmployee->designation_id;
        $oldSchedule = $targetEmployee->schedule_id;

        $oldSalary = $targetEmployee->salary;
        $oldBasic = $oldSalary ? $oldSalary->basic : null;

        $targetEmployee->update($data);
        $salary = $targetEmployee->salary ?: new Salary();
        $salary->fill($data);
        $targetEmployee->salary()->save($salary);

        // Audit log salary changes
        if (isset($data['basic']) && $oldBasic !== null && (float)$oldBasic !== (float)$data['basic']) {
            \App\Models\EmployeeHistory::log(
                $targetEmployee->id,
                'salary_increment',
                'Salary Revision',
                "Basic salary revised from € " . number_format((float)$oldBasic, 2) . " to € " . number_format((float)$data['basic'], 2)
            );
        }

        // Audit log key changes
        if (isset($data['department_id']) && $oldDept != $data['department_id']) {
            $newDept = Department::find($data['department_id']);
            \App\Models\EmployeeHistory::log(
                $targetEmployee->id,
                'department_change',
                'Department Reassigned',
                "Transferred to " . ($newDept->title ?? 'New Department')
            );
        }

        if (isset($data['designation_id']) && $oldDesig != $data['designation_id']) {
            $newDesig = Designation::find($data['designation_id']);
            \App\Models\EmployeeHistory::log(
                $targetEmployee->id,
                'promotion',
                'Designation Updated',
                "Promoted/Changed to " . ($newDesig->title ?? 'New Designation')
            );
        }

        if (isset($data['schedule_id']) && $oldSchedule != $data['schedule_id']) {
            $newSch = Schedule::find($data['schedule_id']);
            \App\Models\EmployeeHistory::log(
                $targetEmployee->id,
                'shift_change',
                'Primary Shift Updated',
                "Shift updated to " . ($newSch->title ?? 'New Schedule')
            );
        }

        return back()->with('success', 'Employee updated successfully');
    }

    /**
     * Remove the specified resource from storage (soft archival).
     */
    public function destroy($employee)
    {
        $targetEmployee = $employee instanceof Employee && $employee->exists
            ? $employee
            : Employee::findOrFail(request()->route('id') ?? (is_numeric($employee) ? $employee : $employee->id));

        \App\Models\EmployeeHistory::log(
            $targetEmployee->id,
            'status_change',
            'Employee Archived',
            "Employee record archived (soft-deleted) by " . (auth()->check() ? auth()->user()->name : 'Admin') . "."
        );

        $targetEmployee->delete();
        return back()->with('success','Employee archived successfully without losing historical payroll or attendance records.');
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
                'departs', 'lateTime', 'overTime',
                'shiftRosters.schedule', 'histories',
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
                        'departs', 'lateTime', 'overTime',
                        'shiftRosters.schedule', 'histories',
                    ])->find($results->first()->id);
                    $results = collect();
                }
            }
        }

        return view('admin.employee.search', compact('query', 'employee', 'results'));
    }
}
