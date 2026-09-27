<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\Schedule;
use App\Models\ShiftRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftRosterController extends Controller
{
    /**
     * Display shift roster and daily/night/extra shift management.
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', today()->format('Y-m-d'));
        $selectedMonth = $request->input('month', today()->format('Y-m'));
        $shiftTypeFilter = $request->input('shift_type', 'all');
        $employeeFilter = $request->input('employee_id');

        $query = ShiftRoster::with(['employee.department', 'employee.designation', 'schedule'])
            ->orderBy('shift_date', 'desc')
            ->orderBy('shift_type');

        if ($request->filled('date')) {
            $query->whereDate('shift_date', $selectedDate);
        } elseif ($request->filled('month')) {
            $carbonMonth = Carbon::parse($selectedMonth . '-01');
            $query->whereBetween('shift_date', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString()
            ]);
        }

        if ($shiftTypeFilter !== 'all') {
            $query->where('shift_type', $shiftTypeFilter);
        }

        if ($employeeFilter) {
            $query->where('employee_id', $employeeFilter);
        }

        $rosters = $query->paginate(25)->withQueryString();

        // Metric cards for today or selected month
        $monthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $monthEnd = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        $stats = [
            'total_shifts' => ShiftRoster::whereBetween('shift_date', [$monthStart, $monthEnd])->count(),
            'day_shifts' => ShiftRoster::whereBetween('shift_date', [$monthStart, $monthEnd])->where('shift_type', 'day')->count(),
            'night_shifts' => ShiftRoster::whereBetween('shift_date', [$monthStart, $monthEnd])->where('shift_type', 'night')->count(),
            'extra_shifts' => ShiftRoster::whereBetween('shift_date', [$monthStart, $monthEnd])->where('shift_type', 'extra')->count(),
            'today_day' => ShiftRoster::whereDate('shift_date', today())->where('shift_type', 'day')->count(),
            'today_night' => ShiftRoster::whereDate('shift_date', today())->where('shift_type', 'night')->count(),
            'today_extra' => ShiftRoster::whereDate('shift_date', today())->where('shift_type', 'extra')->count(),
        ];

        $employees = Employee::where('status', 1)->orderBy('firstname')->get();
        $schedules = Schedule::all();

        return view('admin.schedule.roster', compact(
            'rosters',
            'stats',
            'employees',
            'schedules',
            'selectedDate',
            'selectedMonth',
            'shiftTypeFilter',
            'employeeFilter'
        ));
    }

    /**
     * Store a newly assigned shift roster.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'schedule_id' => 'required|exists:schedules,id',
            'shift_date' => 'required|date',
            'shift_type' => 'required|in:day,night,extra',
            'hours' => 'nullable|numeric|min:0.5|max:24',
            'extra_allowance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);
        $hours = $request->filled('hours') ? (float) $request->hours : $schedule->duration_hours;

        $roster = ShiftRoster::create([
            'employee_id' => $request->employee_id,
            'schedule_id' => $request->schedule_id,
            'shift_date' => $request->shift_date,
            'shift_type' => $request->shift_type,
            'hours' => $hours,
            'extra_allowance' => $request->input('extra_allowance', 0.00),
            'notes' => $request->notes,
            'status' => 'scheduled',
        ]);

        $employee = Employee::find($request->employee_id);
        EmployeeHistory::log(
            $employee->id,
            'shift_change',
            ucfirst($request->shift_type) . " Shift Assigned ({$request->shift_date})",
            "Assigned to schedule '{$schedule->title}' ({$schedule->time_in} - {$schedule->time_out})" .
            ($request->shift_type === 'extra' ? " with extra allowance of € " . number_format($request->extra_allowance ?? 0, 2) : ""),
            ['roster_id' => $roster->id, 'shift_type' => $request->shift_type]
        );

        return back()->with('success', ucfirst($request->shift_type) . ' shift assigned successfully for ' . $employee->firstname . ' ' . $employee->lastname . '.');
    }

    /**
     * Quick assign extra shift.
     */
    public function quickAssignExtra(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_date' => 'required|date',
            'schedule_id' => 'required|exists:schedules,id',
            'extra_allowance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $schedule = Schedule::findOrFail($request->schedule_id);

        $roster = ShiftRoster::create([
            'employee_id' => $request->employee_id,
            'schedule_id' => $schedule->id,
            'shift_date' => $request->shift_date,
            'shift_type' => 'extra',
            'hours' => $schedule->duration_hours,
            'extra_allowance' => $request->input('extra_allowance', 0.00),
            'notes' => $request->input('notes', 'Extra shift duty'),
            'status' => 'scheduled',
        ]);

        $employee = Employee::find($request->employee_id);
        EmployeeHistory::log(
            $employee->id,
            'shift_change',
            "Extra Shift Scheduled on {$request->shift_date}",
            "Assigned extra shift '{$schedule->title}' with € " . number_format($request->extra_allowance ?? 0, 2) . " extra allowance.",
            ['roster_id' => $roster->id, 'shift_type' => 'extra']
        );

        return back()->with('success', 'Extra shift assigned successfully.');
    }

    /**
     * Update shift roster status (completed, swapped, absent).
     */
    public function updateStatus(Request $request, $id)
    {
        $roster = ShiftRoster::findOrFail($id);
        $status = $request->input('status', 'completed');
        $roster->update(['status' => $status]);

        return back()->with('success', 'Shift status updated to ' . ucfirst($status) . '.');
    }

    /**
     * Delete shift roster assignment.
     */
    public function destroy($id)
    {
        $roster = ShiftRoster::findOrFail($id);
        $roster->delete();

        return back()->with('success', 'Shift roster assignment removed.');
    }
}
