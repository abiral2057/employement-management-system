<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LateTime;
use App\Models\Leave;
use App\Models\OverTime;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $departments = Department::where('status', 1)->get();
        $departmentChart = [
            'labels' => $departments->pluck('title'),
            'counts' => $departments->map(fn ($department) => Employee::where('department_id', $department->id)->where('status', 1)->count()),
        ];

        $designations = Designation::where('status', 1)->get();
        $designationChart = [
            'labels' => $designations->pluck('title'),
            'counts' => $designations->map(fn ($designation) => Employee::where('designation_id', $designation->id)->where('status', 1)->count()),
        ];

        $genderChart = [
            'labels' => ['Male', 'Female'],
            'counts' => [
                Employee::where('gender', 1)->where('status', 1)->count(),
                Employee::where('gender', 2)->where('status', 1)->count(),
            ],
        ];

        $attendanceTrend = collect(range(6, 0))->map(function ($i) {
            $date = now()->subDays($i);

            return [
                'label' => $date->format('d M'),
                'present' => Attendance::where('attendance_date', $date->format('Y-m-d'))->where('status', 1)->count(),
            ];
        });

        $payrollTrend = collect(range(5, 0))->map(function ($i) {
            $month = now()->firstOfMonth()->subMonths($i);

            return [
                'label' => $month->format('M Y'),
                'total' => Payroll::whereMonth('month', $month->format('m'))
                    ->whereYear('year', $month->format('Y'))
                    ->sum('net_salary'),
            ];
        });

        $users = User::with('role')->latest()->take(5)->get();
        $employees = Employee::with(['department', 'designation'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'departmentChart',
            'designationChart',
            'genderChart',
            'attendanceTrend',
            'payrollTrend',
            'users',
            'employees'
        ));
    }

    /**
     * Show the performance report.
     */
    public function performanceReport(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : today()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->to)->endOfDay() : today()->endOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $workingDays = 0;
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to->copy()->endOfDay())) {
            if (! $cursor->isFriday()) {
                $workingDays++;
            }
            $cursor->addDay();
        }

        $employees = Employee::with(['designation', 'department'])
            ->where('status', 1)
            ->get()
            ->map(function ($employee) use ($from, $to, $workingDays) {
                $present = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                    ->where('status', 1)
                    ->count();

                $absent = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('attendance_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                    ->where('status', 0)
                    ->count();

                $lateCount = LateTime::where('employee_id', $employee->id)
                    ->whereBetween('latetime_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                    ->count();

                $overTimeMinutes = OverTime::where('employee_id', $employee->id)
                    ->whereBetween('overtime_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
                    ->sum('overtime_duration');

                $overTimeHours = intdiv($overTimeMinutes, 60);
                $overTimeRemainder = $overTimeMinutes % 60;

                $approvedLeaves = Leave::where('employee_id', $employee->id)
                    ->where('status', 1)
                    ->get()
                    ->sum(function ($leave) use ($from, $to) {
                        $start = Carbon::parse($leave->start_date)->max($from);
                        $end = Carbon::parse($leave->end_date)->min($to->copy()->startOfDay());

                        return $start->lte($end) ? $start->diffInDays($end) + 1 : 0;
                    });

                $performance = $workingDays > 0 ? round(($present / $workingDays) * 100) : 0;

                return [
                    'employee' => $employee,
                    'present' => $present,
                    'absent' => $absent,
                    'lateCount' => $lateCount,
                    'overTime' => sprintf('%dh %02dm', $overTimeHours, $overTimeRemainder),
                    'leaveDays' => $approvedLeaves,
                    'performance' => $performance,
                ];
            });

        return view('admin.performance', compact('employees', 'from', 'to', 'workingDays'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('admin.department');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}