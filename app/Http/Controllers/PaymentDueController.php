<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentDueController extends Controller
{
    /**
     * Display Payment Due and Payment Reminder Center.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));
        $statusFilter = $request->input('status', 'all');
        $departmentFilter = $request->input('department_id');

        $query = Payroll::with(['employee.department', 'employee.designation'])
            ->where('year', $selectedYear)
            ->where('month', (int) $selectedMonth)
            ->orderBy('payment_status')
            ->orderBy('due_date');

        if ($departmentFilter) {
            $query->whereHas('employee', function ($q) use ($departmentFilter) {
                $q->where('department_id', $departmentFilter);
            });
        }

        if ($statusFilter === 'unpaid') {
            $query->where('payment_status', 'unpaid');
        } elseif ($statusFilter === 'partial') {
            $query->where('payment_status', 'partial');
        } elseif ($statusFilter === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($statusFilter === 'overdue') {
            $query->where('payment_status', '!=', 'paid')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', today()->toDateString());
        }

        $payrolls = $query->paginate(25)->withQueryString();

        // KPIs for the selected month/year
        $monthPayrolls = Payroll::where('year', $selectedYear)
            ->where('month', (int) $selectedMonth)
            ->get();

        $totalLiability = $monthPayrolls->sum('net_salary');
        $totalPaid = $monthPayrolls->sum('paid_amount');
        $totalOutstanding = max(0, $totalLiability - $totalPaid);

        $overdueCount = $monthPayrolls->filter(fn($p) => $p->isOverdue())->count();
        $unpaidCount = $monthPayrolls->where('payment_status', 'unpaid')->count();
        $partialCount = $monthPayrolls->where('payment_status', 'partial')->count();
        $paidCount = $monthPayrolls->where('payment_status', 'paid')->count();

        $departments = Department::where('status', 1)->get();
        $allEmployees = Employee::with('salary')->where('status', 1)->orderBy('firstname')->get();

        $stats = [
            'total_liability' => $totalLiability,
            'total_paid' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'overdue_count' => $overdueCount,
            'unpaid_count' => $unpaidCount,
            'partial_count' => $partialCount,
            'paid_count' => $paidCount,
            'total_employees' => $monthPayrolls->count(),
        ];

        return view('admin.payroll.due', compact(
            'payrolls',
            'stats',
            'departments',
            'allEmployees',
            'selectedYear',
            'selectedMonth',
            'statusFilter',
            'departmentFilter'
        ));
    }

    /**
     * Record a full or partial payment for a payroll record.
     */
    public function recordPayment(Request $request, $id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $request->validate([
            'amount_paying' => 'required|numeric|min:1',
            'payment_method' => 'required|string|max:50',
            'paid_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $amountPaying = (float) $request->amount_paying;
        $currentPaid = (float) $payroll->paid_amount;
        $newTotalPaid = $currentPaid + $amountPaying;
        $netSalary = (float) $payroll->net_salary;

        if ($newTotalPaid >= $netSalary) {
            $payroll->paid_amount = $netSalary;
            $payroll->payment_status = 'paid';
            $payroll->status = 'paid';
        } else {
            $payroll->paid_amount = $newTotalPaid;
            $payroll->payment_status = 'partial';
            $payroll->status = 'approved';
        }

        $payroll->paid_date = $request->paid_date;
        $payroll->payment_method = $request->payment_method;
        if ($request->filled('notes')) {
            $payroll->notes = trim(($payroll->notes ? $payroll->notes . "\n" : '') . "[" . date('d M Y') . "] " . $request->notes);
        }
        $payroll->save();

        // Log history
        $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
        $monthName = $months[$payroll->month] ?? $payroll->month;

        EmployeeHistory::log(
            $payroll->employee_id,
            'salary_update',
            "Salary Payment Recorded (€ " . number_format($amountPaying, 2) . ")",
            "Paid € " . number_format($amountPaying, 2) . " via {$request->payment_method} for {$monthName} {$payroll->year}. Remaining balance: € " . number_format($payroll->balance_due, 2) . ". Status: " . strtoupper($payroll->payment_status),
            ['payroll_id' => $payroll->id, 'amount_paid' => $amountPaying, 'status' => $payroll->payment_status]
        );

        return back()->with('success', 'Payment of € ' . number_format($amountPaying, 2) . ' recorded successfully for ' . $payroll->employee->firstname . ' ' . $payroll->employee->lastname . '.');
    }

    /**
     * Send payment reminder notice to an employee.
     */
    public function sendReminder(Request $request, $id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $payroll->reminder_count = (int) $payroll->reminder_count + 1;
        $payroll->last_reminder_at = now();
        $payroll->save();

        $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
        $period = ($months[$payroll->month] ?? $payroll->month) . ' ' . $payroll->year;

        EmployeeHistory::log(
            $payroll->employee_id,
            'payment_reminder',
            "Payment Reminder #{$payroll->reminder_count} Sent for {$period}",
            "Automated payment reminder notice generated for pending balance of € " . number_format($payroll->balance_due, 2) . " (Due: " . ($payroll->due_date ? Carbon::parse($payroll->due_date)->format('d M Y') : 'Pending') . ")",
            ['payroll_id' => $payroll->id, 'reminder_num' => $payroll->reminder_count, 'due_amount' => $payroll->balance_due]
        );

        return back()->with('success', "Payment reminder #{$payroll->reminder_count} generated and logged for {$payroll->employee->firstname} {$payroll->employee->lastname}.");
    }

    /**
     * Batch send reminders to all overdue employees for the selected period.
     */
    public function batchReminder(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));

        $overduePayrolls = Payroll::with('employee')
            ->where('year', $selectedYear)
            ->where('month', (int) $selectedMonth)
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', today()->toDateString())
            ->get();

        if ($overduePayrolls->isEmpty()) {
            return back()->with('info', 'No overdue salaries found for this period.');
        }

        $count = 0;
        foreach ($overduePayrolls as $payroll) {
            $payroll->reminder_count = (int) $payroll->reminder_count + 1;
            $payroll->last_reminder_at = now();
            $payroll->save();

            EmployeeHistory::log(
                $payroll->employee_id,
                'payment_reminder',
                "Batch Payment Reminder Sent",
                "Automated batch overdue reminder dispatched for pending balance of € " . number_format($payroll->balance_due, 2),
                ['payroll_id' => $payroll->id, 'reminder_num' => $payroll->reminder_count]
            );
            $count++;
        }

        return back()->with('success', "Batch payment reminders dispatched to {$count} overdue employee(s).");
    }

    /**
     * Disburse Advance Salary, Mid-Month Early Pay, or Instant Overtime Payment.
     * Can be performed ANY day of the month before month-end.
     */
    public function recordAdvancePayment(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.5',
            'payment_type' => 'required|in:advance_salary,overtime_payment,bonus_payout,interim_salary',
            'payment_method' => 'required|string|max:50',
            'paid_date' => 'required|date',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer|between:1,12',
            'notes' => 'nullable|string|max:500',
        ]);

        $employee = Employee::with('salary')->findOrFail($request->employee_id);
        $paidDate = Carbon::parse($request->paid_date);
        $year = (int) $request->input('year', $paidDate->year);
        $month = (int) $request->input('month', $paidDate->month);
        $amount = (float) $request->amount;
        $type = $request->payment_type;
        $method = $request->payment_method;

        // Check if a Payroll record exists for this employee for the selected period
        $payroll = Payroll::where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$payroll) {
            // It's not month-end yet, so automatically initialize the payroll record from employee's contract
            $sal = $employee->salary;
            $basic = (float) ($sal->basic ?? 0);
            $houseRent = (float) ($sal->house_rent ?? 0);
            $medical = (float) ($sal->medical ?? 0);
            $transport = (float) ($sal->transport ?? 0);
            $phone = (float) ($sal->phone_bill ?? 0);
            $internet = (float) ($sal->internet_bill ?? 0);
            $special = (float) ($sal->special ?? 0);
            $bonus = $type === 'bonus_payout' ? $amount : 0.00;
            $otPay = $type === 'overtime_payment' ? $amount : 0.00;

            $gross = $basic + $houseRent + $medical + $transport + $phone + $internet + $special + $bonus + $otPay;
            $pf = (float) ($sal->provident_fund ?? 0);
            $tax = (float) ($sal->income_tax ?? 0);
            $health = (float) ($sal->health_insurance ?? 0);
            $life = (float) ($sal->life_insurance ?? 0);
            $deductions = $pf + $tax + $health + $life;
            $net = max(0, $gross - $deductions);

            $defaultDueDate = Carbon::createFromDate($year, $month, 1)->addMonth()->startOfMonth()->addDays(6)->toDateString();

            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
                'basic' => $basic,
                'house_rent' => $houseRent,
                'medical' => $medical,
                'transport' => $transport,
                'phone_bill' => $phone,
                'internet_bill' => $internet,
                'special' => $special,
                'bonus' => $bonus,
                'overtime_pay' => $otPay,
                'provident_fund' => $pf,
                'income_tax' => $tax,
                'health_insurance' => $health,
                'life_insurance' => $life,
                'gross_salary' => $gross,
                'deduction' => $deductions,
                'net_salary' => $net,
                'status' => 'approved',
                'due_date' => $defaultDueDate,
                'paid_amount' => 0.00,
                'payment_status' => 'unpaid',
            ]);
        } else {
            if ($type === 'overtime_payment') {
                $payroll->overtime_pay = (float) ($payroll->overtime_pay ?? 0) + $amount;
                $payroll->gross_salary = (float) ($payroll->gross_salary ?? 0) + $amount;
                $payroll->net_salary = max(0, (float) ($payroll->net_salary ?? 0) + $amount);
            } elseif ($type === 'bonus_payout') {
                $payroll->bonus = (float) ($payroll->bonus ?? 0) + $amount;
                $payroll->gross_salary = (float) ($payroll->gross_salary ?? 0) + $amount;
                $payroll->net_salary = max(0, (float) ($payroll->net_salary ?? 0) + $amount);
            }
        }

        // Record the advance payment / payout
        $newTotalPaid = (float) ($payroll->paid_amount ?? 0) + $amount;
        $netSalary = (float) $payroll->net_salary;

        $payroll->paid_amount = $newTotalPaid;
        if ($newTotalPaid >= $netSalary && $netSalary > 0) {
            $payroll->payment_status = 'paid';
            $payroll->status = 'paid';
        } else {
            $payroll->payment_status = 'partial';
            $payroll->status = 'approved';
        }

        $payroll->paid_date = $paidDate->toDateString();
        $payroll->payment_method = $method;

        $typeLabel = match($type) {
            'advance_salary' => 'Advance Salary',
            'overtime_payment' => 'Overtime Payment',
            'bonus_payout' => 'Bonus Payout',
            default => 'Early Salary Disbursement',
        };

        $noteEntry = "[" . date('d M Y') . "] " . $typeLabel . " of €" . number_format($amount, 2) . " disbursed via " . $method . ($request->notes ? ": " . $request->notes : ".");
        $payroll->notes = trim(($payroll->notes ? $payroll->notes . "\n" : '') . $noteEntry);
        $payroll->save();

        // If overtime payment, also approve any pending overtime entries up to that amount
        if ($type === 'overtime_payment') {
            \App\Models\OverTime::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->where('amount', '<=', $amount)
                ->update(['status' => 'approved']);
        }

        // Log audit history in EmployeeHistory
        EmployeeHistory::log(
            $employee->id,
            'salary_update',
            "{$typeLabel} (€ " . number_format($amount, 2) . ") Disbursed",
            "Disbursed € " . number_format($amount, 2) . " ({$typeLabel}) on " . $paidDate->format('d M Y') . " via {$method} for {$month}/{$year}. Remaining balance: € " . number_format($payroll->balance_due, 2) . ". Notes: " . ($request->notes ?? 'None'),
            ['payroll_id' => $payroll->id, 'type' => $type, 'amount' => $amount, 'method' => $method]
        );

        return back()->with('success', "{$typeLabel} of € " . number_format($amount, 2) . " successfully disbursed to {$employee->firstname} {$employee->lastname}! (Remaining Balance: € " . number_format($payroll->balance_due, 2) . ").");
    }
}
