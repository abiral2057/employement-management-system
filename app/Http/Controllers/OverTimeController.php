<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\OverTime;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class OverTimeController extends Controller
{
    /**
     * Display a listing of overtime records.
     */
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', today()->format('Y-m'));
        $statusFilter = $request->input('status', 'all');
        $employeeFilter = $request->input('employee_id');

        $query = OverTime::with(['employee.department', 'employee.designation', 'employee.salary'])
            ->orderBy('overtime_date', 'desc');

        if ($request->filled('month')) {
            $carbonMonth = Carbon::parse($selectedMonth . '-01');
            $query->whereBetween('overtime_date', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString()
            ]);
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($employeeFilter) {
            $query->where('employee_id', $employeeFilter);
        }

        $overtimes = $query->paginate(25)->withQueryString();

        // Overtime Summary KPIs for the month
        $monthStart = Carbon::parse($selectedMonth . '-01')->startOfMonth()->toDateString();
        $monthEnd = Carbon::parse($selectedMonth . '-01')->endOfMonth()->toDateString();

        $monthRecords = OverTime::whereBetween('overtime_date', [$monthStart, $monthEnd])->get();

        $totalApprovedMinutes = 0;
        $totalApprovedCost = 0;
        $pendingCount = 0;

        foreach ($monthRecords as $ot) {
            if ($ot->status === 'approved') {
                $parts = explode(':', $ot->overtime_duration);
                $h = isset($parts[0]) ? (int) $parts[0] : 0;
                $m = isset($parts[1]) ? (int) $parts[1] : 0;
                $totalApprovedMinutes += ($h * 60) + $m;
                $totalApprovedCost += (float) ($ot->amount ?? 0);
            } elseif ($ot->status === 'pending') {
                $pendingCount++;
            }
        }

        $stats = [
            'total_hours' => round($totalApprovedMinutes / 60, 2),
            'total_cost' => $totalApprovedCost,
            'pending_claims' => $pendingCount,
            'total_records' => $monthRecords->count(),
        ];

        $employees = Employee::with('salary')->where('status', 1)->orderBy('firstname')->get();

        return view('admin.attendance.overtime', compact(
            'overtimes',
            'stats',
            'employees',
            'selectedMonth',
            'statusFilter',
            'employeeFilter'
        ));
    }

    /**
     * Store a manually created overtime record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'overtime_date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'rate' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = Employee::with('salary')->findOrFail($request->employee_id);

        $hours = (float) $request->hours;
        $h = floor($hours);
        $m = round(($hours - $h) * 60);
        $durationFormatted = sprintf('%02d:%02d:00', $h, $m);

        // Determine rate: requested rate, or employee overtime_rate, or calculate basic / 208
        $hourlyRate = $request->filled('rate') && $request->rate > 0
            ? (float) $request->rate
            : (float) ($employee->salary->overtime_rate ?? 0);

        if ($hourlyRate <= 0 && isset($employee->salary->basic) && $employee->salary->basic > 0) {
            $hourlyRate = round($employee->salary->basic / (26 * 8), 2); // default hourly rate based on 26 days * 8 hours
        }

        $amount = round($hours * $hourlyRate, 2);

        $overtime = OverTime::create([
            'employee_id' => $employee->id,
            'overtime_date' => $request->overtime_date,
            'overtime_duration' => $durationFormatted,
            'status' => 'approved',
            'rate' => $hourlyRate,
            'amount' => $amount,
            'reason' => $request->reason ?? 'Manual Overtime Entry',
        ]);

        EmployeeHistory::log(
            $employee->id,
            'salary_update',
            "Overtime Logged ({$hours} hrs on {$request->overtime_date})",
            "Logged {$hours} hours at € {$hourlyRate}/hr (Total: € " . number_format($amount, 2) . "). Reason: " . ($request->reason ?? 'General overtime work'),
            ['overtime_id' => $overtime->id, 'amount' => $amount]
        );

        return back()->with('success', 'Overtime logged and approved successfully for ' . $employee->firstname . ' ' . $employee->lastname . '.');
    }

    /**
     * Approve an overtime claim.
     */
    public function approve($id)
    {
        $overtime = OverTime::with('employee.salary')->findOrFail($id);

        if (!$overtime->amount || $overtime->amount <= 0) {
            $hours = $overtime->hours_decimal;
            $rate = (float) ($overtime->rate ?: ($overtime->employee->salary->overtime_rate ?? 0));
            if ($rate <= 0 && isset($overtime->employee->salary->basic)) {
                $rate = round($overtime->employee->salary->basic / 208, 2);
            }
            $overtime->rate = $rate;
            $overtime->amount = round($hours * $rate, 2);
        }

        $overtime->status = 'approved';
        $overtime->save();

        EmployeeHistory::log(
            $overtime->employee_id,
            'salary_update',
            "Overtime Approved for {$overtime->overtime_date}",
            "Approved {$overtime->overtime_duration} duration (€ " . number_format($overtime->amount, 2) . ")",
            ['overtime_id' => $overtime->id]
        );

        return back()->with('success', 'Overtime claim approved.');
    }

    /**
     * Instantly disburse overtime payment even if it is not month-end.
     */
    public function payout(Request $request, $id)
    {
        $overtime = OverTime::with('employee.salary')->findOrFail($id);

        if (!$overtime->amount || $overtime->amount <= 0) {
            $hours = $overtime->hours_decimal;
            $rate = (float) ($overtime->rate ?: ($overtime->employee->salary->overtime_rate ?? 0));
            if ($rate <= 0 && isset($overtime->employee->salary->basic)) {
                $rate = round($overtime->employee->salary->basic / 208, 2);
            }
            $overtime->rate = $rate;
            $overtime->amount = round($hours * $rate, 2);
        }

        $overtime->status = 'approved';
        $overtime->save();

        $amount = (float) $overtime->amount;
        $employee = $overtime->employee;
        $otDate = Carbon::parse($overtime->overtime_date);
        $year = $otDate->year;
        $month = $otDate->month;
        $method = $request->input('payment_method', 'Cash');

        $payroll = \App\Models\Payroll::where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$payroll) {
            $sal = $employee->salary;
            $basic = (float) ($sal->basic ?? 0);
            $houseRent = (float) ($sal->house_rent ?? 0);
            $medical = (float) ($sal->medical ?? 0);
            $gross = $basic + $houseRent + $medical + $amount;
            $net = $gross;
            $dueDate = Carbon::createFromDate($year, $month, 1)->addMonth()->startOfMonth()->addDays(6)->toDateString();

            $payroll = \App\Models\Payroll::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
                'basic' => $basic,
                'house_rent' => $houseRent,
                'medical' => $medical,
                'overtime_pay' => $amount,
                'gross_salary' => $gross,
                'net_salary' => $net,
                'status' => 'approved',
                'due_date' => $dueDate,
                'paid_amount' => $amount,
                'payment_status' => 'partial',
                'paid_date' => today()->toDateString(),
                'payment_method' => $method,
                'notes' => "[" . date('d M Y') . "] Overtime payout of €" . number_format($amount, 2) . " paid via {$method}.",
            ]);
        } else {
            $payroll->overtime_pay = (float) ($payroll->overtime_pay ?? 0) + $amount;
            $payroll->gross_salary = (float) ($payroll->gross_salary ?? 0) + $amount;
            $payroll->net_salary = max(0, (float) ($payroll->net_salary ?? 0) + $amount);
            $payroll->paid_amount = (float) ($payroll->paid_amount ?? 0) + $amount;
            $payroll->paid_date = today()->toDateString();
            $payroll->payment_method = $method;
            if ($payroll->paid_amount >= $payroll->net_salary && $payroll->net_salary > 0) {
                $payroll->payment_status = 'paid';
                $payroll->status = 'paid';
            } else {
                $payroll->payment_status = 'partial';
            }
            $payroll->notes = trim(($payroll->notes ? $payroll->notes . "\n" : '') . "[" . date('d M Y') . "] Overtime payout of €" . number_format($amount, 2) . " paid via {$method}.");
            $payroll->save();
        }

        EmployeeHistory::log(
            $employee->id,
            'salary_update',
            "Overtime Payout (€ " . number_format($amount, 2) . ") Disbursed",
            "Paid € " . number_format($amount, 2) . " for {$overtime->overtime_duration} OT on {$overtime->overtime_date} via {$method}.",
            ['overtime_id' => $overtime->id, 'amount' => $amount, 'method' => $method]
        );

        return back()->with('success', "Overtime payment of € " . number_format($amount, 2) . " disbursed to {$employee->firstname} {$employee->lastname} via {$method}.");
    }

    /**
     * Reject an overtime claim.
     */
    public function reject($id)
    {
        $overtime = OverTime::findOrFail($id);
        $overtime->status = 'rejected';
        $overtime->save();

        return back()->with('success', 'Overtime claim rejected.');
    }

    /**
     * Remove overtime record.
     */
    public function destroy($id)
    {
        $overtime = OverTime::findOrFail($id);
        $overtime->delete();

        return back()->with('success', 'Overtime entry deleted successfully.');
    }

    /**
     * Auto-calculate overtime from attendance departure.
     */
    public static function overTime(Employee $employee)
    {
        $schedule = $employee->schedule;
        if (!$schedule || !$schedule->time_out) {
            return;
        }

        $current_t = new DateTime(date('H:i:s'));
        $scheduled_out = new DateTime($schedule->time_out);

        // If departure is after scheduled time out
        if ($current_t > $scheduled_out) {
            $diff = $scheduled_out->diff($current_t);
            $hours = $diff->h + ($diff->i / 60);

            if ($hours >= 0.25) { // Only log if at least 15 minutes of overtime
                $difference = $diff->format('%H:%I:%S');

                $rate = (float) ($employee->salary->overtime_rate ?? 0);
                if ($rate <= 0 && isset($employee->salary->basic)) {
                    $rate = round($employee->salary->basic / (26 * 8), 2);
                }

                $amount = round($hours * $rate, 2);

                $overtime = new OverTime();
                $overtime->employee_id = $employee->id;
                $overtime->overtime_duration = $difference;
                $overtime->overtime_date = date('Y-m-d');
                $overtime->status = 'approved';
                $overtime->rate = $rate;
                $overtime->amount = $amount;
                $overtime->reason = 'Auto-calculated checkout overtime';
                $overtime->save();
            }
        }
    }
}
