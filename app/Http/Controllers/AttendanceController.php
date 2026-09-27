<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance.
     */
    public function index()
    {
        $employees = Employee::with(['department', 'designation', 'schedule'])->where('status', 1)->get();
        return view('admin.attendance.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', 1)->get();
        return view('admin.attendance.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'attendance_time' => 'required',
            'status' => 'nullable|in:0,1',
        ]);

        Attendance::create($data);
        return back()->with('success', 'Attendance record created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);
        $employees = Employee::where('status', 1)->get();
        return view('admin.attendance.edit', compact(['employees', 'attendance']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $attendance = $id instanceof Attendance ? $id : Attendance::findOrFail($id);
        $attendance->update($request->all());
        return back()->with('success', 'Attendance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $attendance = $id instanceof Attendance ? $id : Attendance::findOrFail($id);
        $attendance->delete();
        return back()->with('success', 'Attendance record deleted successfully.');
    }

    public function report(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        $carbon = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $carbon->daysInMonth;

        $employees = Employee::with('attendances')->where('status', 1)->get();

        $userAttendanceData = [];

        foreach ($employees as $employee) {
            $userAttendanceData[$employee->id] = [
                'present' => 0,
                'absent' => 0,
            ];

            $attendances = $employee->attendances->filter(function ($att) use ($year, $month) {
                $d = $att->attendance_date ?? $att->date;
                if (!$d) return false;
                $c = Carbon::parse($d);
                return $c->year == $year && $c->month == (int) $month;
            });

            foreach ($attendances as $att) {
                if ($att->status == 1 || $att->status === '1') {
                    $userAttendanceData[$employee->id]['present']++;
                } else {
                    $userAttendanceData[$employee->id]['absent']++;
                }
            }
        }

        $date = $carbon;
        return view('admin.attendance.report', compact('employees', 'daysInMonth', 'userAttendanceData', 'date', 'year', 'month'));
    }

    public function barcode()
    {
        return view('admin.attendance.barcode');
    }
}
