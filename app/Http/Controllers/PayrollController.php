<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\OverTime;
use App\Models\Payroll;
use App\Models\ShiftRoster;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));

        $employees = Employee::with(['designation', 'department', 'salary'])->where('status', 1)->get();

        // Calculate or retrieve existing payrolls for the period
        $existingPayrolls = Payroll::where('year', $selectedYear)
            ->where('month', (int) $selectedMonth)
            ->get()
            ->keyBy('employee_id');

        return view('admin.payroll.index', compact('employees', 'existingPayrolls', 'selectedYear', 'selectedMonth'));
    }

    /**
     * Show the form for creating/generating a new payroll run.
     */
    public function create(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));

        $carbonPeriod = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
        $daysInMonth = $carbonPeriod->daysInMonth;
        $monthStart = $carbonPeriod->copy()->startOfMonth()->toDateString();
        $monthEnd = $carbonPeriod->copy()->endOfMonth()->toDateString();

        $employees = Employee::with(['designation', 'department', 'salary', 'schedule'])
            ->where('status', 1)
            ->get()
            ->map(function ($emp) use ($monthStart, $monthEnd, $daysInMonth) {
                // Present attendance count
                $present = Attendance::where('employee_id', $emp->id)
                    ->whereBetween('attendance_date', [$monthStart, $monthEnd])
                    ->where('status', 1)
                    ->count();

                $absent = max(0, $daysInMonth - $present);

                // Approved Overtime
                $approvedOvertimes = OverTime::where('employee_id', $emp->id)
                    ->whereBetween('overtime_date', [$monthStart, $monthEnd])
                    ->where('status', 'approved')
                    ->get();

                $otHours = 0;
                $otPay = 0;
                foreach ($approvedOvertimes as $ot) {
                    $otHours += $ot->hours_decimal;
                    $otPay += (float) $ot->amount;
                }

                // Extra Shift allowances
                $extraShifts = ShiftRoster::where('employee_id', $emp->id)
                    ->whereBetween('shift_date', [$monthStart, $monthEnd])
                    ->where('shift_type', 'extra')
                    ->get();

                $extraShiftAllowance = $extraShifts->sum('extra_allowance');

                $sal = $emp->salary;
                $basic = (float) ($sal->basic ?? 0);
                $houseRent = (float) ($sal->house_rent ?? 0);
                $medical = (float) ($sal->medical ?? 0);
                $transport = (float) ($sal->transport ?? 0);
                $phone = (float) ($sal->phone_bill ?? 0);
                $internet = (float) ($sal->internet_bill ?? 0);
                $special = (float) ($sal->special ?? 0);
                $bonus = (float) ($sal->bonus ?? 0);

                $gross = $basic + $houseRent + $medical + $transport + $phone + $internet + $special + $bonus + $otPay + $extraShiftAllowance;

                $pf = (float) ($sal->provident_fund ?? 0);
                $tax = (float) ($sal->income_tax ?? 0);
                $health = (float) ($sal->health_insurance ?? 0);
                $life = (float) ($sal->life_insurance ?? 0);
                $deductions = $pf + $tax + $health + $life;

                $net = max(0, $gross - $deductions);

                return [
                    'employee' => $emp,
                    'present' => $present,
                    'absent' => $absent,
                    'ot_hours' => round($otHours, 2),
                    'ot_pay' => round($otPay, 2),
                    'extra_shift_allowance' => round($extraShiftAllowance, 2),
                    'basic' => $basic,
                    'house_rent' => $houseRent,
                    'medical' => $medical,
                    'transport' => $transport,
                    'phone_bill' => $phone,
                    'internet_bill' => $internet,
                    'special' => $special,
                    'bonus' => $bonus,
                    'gross' => round($gross, 2),
                    'deductions' => round($deductions, 2),
                    'net' => round($net, 2),
                ];
            });

        return view('admin.payroll.create', compact('employees', 'selectedYear', 'selectedMonth', 'daysInMonth'));
    }

    /**
     * Calculate and save/update payroll for employees with lock protection and DB transactions.
     */
    public function calculatePayroll(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));
        $defaultDueDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->addMonth()->startOfMonth()->addDays(6)->toDateString(); // 7th of next month

        $count = 0;
        $lockedSkipped = 0;

        if (isset($request->payroll) && is_array($request->payroll)) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $selectedYear, $selectedMonth, $defaultDueDate, &$count, &$lockedSkipped) {
                foreach ($request->payroll as $employeeId => $data) {
                    $employee = Employee::find($employeeId);
                    if (!$employee) continue;

                    // Protect locked payroll records
                    $existing = Payroll::where('employee_id', $employeeId)
                        ->where('year', $selectedYear)
                        ->where('month', (int) $selectedMonth)
                        ->first();

                    if ($existing && !empty($existing->is_locked)) {
                        $lockedSkipped++;
                        continue;
                    }

                    $basic = (float) ($data['basic'] ?? 0);
                    $houseRent = (float) ($data['house_rent'] ?? 0);
                    $medical = (float) ($data['medical'] ?? 0);
                    $transport = (float) ($data['transport'] ?? 0);
                    $phone = (float) ($data['phone_bill'] ?? 0);
                    $internet = (float) ($data['internet_bill'] ?? 0);
                    $special = (float) ($data['special'] ?? 0);
                    $bonus = (float) ($data['bonus'] ?? 0);
                    $otHours = (float) ($data['overtime_hours'] ?? 0);
                    $otPay = (float) ($data['overtime_pay'] ?? 0);

                    $gross = $basic + $houseRent + $medical + $transport + $phone + $internet + $special + $bonus + $otPay;

                    $pf = (float) ($data['provident_fund'] ?? 0);
                    $tax = (float) ($data['income_tax'] ?? 0);
                    $health = (float) ($data['health_insurance'] ?? 0);
                    $life = (float) ($data['life_insurance'] ?? 0);
                    $deductions = $pf + $tax + $health + $life;

                    $net = max(0, $gross - $deductions);

                    // Update or create payroll record without duplicating
                    $payroll = Payroll::updateOrCreate(
                        [
                            'employee_id' => $employeeId,
                            'year' => $selectedYear,
                            'month' => (int) $selectedMonth,
                        ],
                        [
                            'basic' => $basic,
                            'house_rent' => $houseRent,
                            'medical' => $medical,
                            'transport' => $transport,
                            'phone_bill' => $phone,
                            'internet_bill' => $internet,
                            'special' => $special,
                            'bonus' => $bonus,
                            'overtime_hours' => $otHours,
                            'overtime_pay' => $otPay,
                            'days_present' => $data['days_present'] ?? null,
                            'days_absent' => $data['days_absent'] ?? null,
                            'gross_salary' => $gross,
                            'provident_fund' => $pf,
                            'income_tax' => $tax,
                            'health_insurance' => $health,
                            'life_insurance' => $life,
                            'deduction' => $deductions,
                            'net_salary' => $net,
                            'status' => 'approved',
                            'due_date' => $defaultDueDate,
                        ]
                    );

                    // Initialize payment status if new
                    if (!$payroll->payment_status) {
                        $payroll->payment_status = 'unpaid';
                        $payroll->paid_amount = 0.00;
                        $payroll->save();
                    }

                    $count++;
                }
            });
        }

        $msg = "Payroll computed successfully for {$count} employee(s).";
        if ($lockedSkipped > 0) {
            $msg .= " ({$lockedSkipped} record(s) were locked and preserved unchanged).";
        }

        return redirect()->route('payroll.due')->with('success', $msg);
    }

    /**
     * Lock or Unlock a finalized payroll record to prevent accidental modifications.
     */
    public function toggleLock($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->is_locked = !$payroll->is_locked;
        $payroll->save();

        $action = $payroll->is_locked ? 'Locked' : 'Unlocked';
        EmployeeHistory::log(
            $payroll->employee_id,
            'salary_update',
            "Payroll {$action}",
            "Payroll record for {$payroll->month}/{$payroll->year} was {$action} by " . (auth()->user()->name ?? 'Admin') . "."
        );

        return back()->with('success', "Payroll record for {$payroll->employee->firstname} {$payroll->employee->lastname} ({$payroll->month}/{$payroll->year}) is now {$action}.");
    }

    public function sheetReport(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));

        $employees = Employee::with(['department', 'designation'])->where('status', 1)->get();
        $salaryData = Payroll::where('year', $selectedYear)
            ->where('month', (int) $selectedMonth)
            ->get()
            ->keyBy('employee_id');

        return view('admin.payroll.report', compact('employees', 'salaryData', 'selectedYear', 'selectedMonth'));
    }

    /**
     * Export payroll sheet as CSV.
     */
    public function exportCsv(Request $request)
    {
        $selectedYear = $request->input('year', date('Y'));
        $selectedMonth = $request->input('month', date('m'));

        $employees = Employee::with(['department', 'designation'])->where('status', 1)->get();
        $salaryData = Payroll::where('year', $selectedYear)
            ->where('month', (int) $selectedMonth)
            ->get()
            ->keyBy('employee_id');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"payroll_sheet_{$selectedYear}_{$selectedMonth}.csv\"",
        ];

        $callback = function () use ($employees, $salaryData) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Employee ID', 'Name', 'Department', 'Designation',
                'Basic', 'Gross Salary', 'Total Deductions', 'Net Salary',
                'Payment Status', 'Paid Amount', 'Balance Due', 'Locked'
            ]);

            foreach ($employees as $emp) {
                $pay = $salaryData->get($emp->id);
                fputcsv($handle, [
                    $emp->unique_id,
                    $emp->firstname . ' ' . $emp->lastname,
                    optional($emp->department)->title ?? '—',
                    optional($emp->designation)->title ?? '—',
                    $pay ? $pay->basic : 0,
                    $pay ? $pay->gross_salary : 0,
                    $pay ? $pay->deduction : 0,
                    $pay ? $pay->net_salary : 0,
                    $pay ? ucfirst($pay->payment_status ?? 'unpaid') : 'Not Run',
                    $pay ? $pay->paid_amount : 0,
                    $pay ? $pay->balance_due : 0,
                    $pay && $pay->is_locked ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generateReport(Request $request)
    {
        return $this->sheetReport($request);
    }
}
