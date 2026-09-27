<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of all payroll payment transactions and statements.
     */
    public function index(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $selectedYear = $request->input('year', $employeeId ? null : date('Y'));
        $selectedMonth = $request->input('month');
        $paymentStatus = $request->input('payment_status');
        $paymentMethod = $request->input('payment_method');
        $search = $request->input('search');

        $query = Payroll::with(['employee.department', 'employee.designation'])
            ->where(function ($q) {
                $q->where('paid_amount', '>', 0)
                  ->orWhere('payment_status', 'paid')
                  ->orWhere('payment_status', 'partial');
            });

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($selectedYear) {
            $query->where('year', $selectedYear);
        }

        if ($selectedMonth) {
            $query->where('month', (int) $selectedMonth);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('unique_id', 'like', "%{$search}%");
            });
        }

        // Summary metrics
        $allTransactions = (clone $query)->get();
        $totalDisbursed = $allTransactions->sum('paid_amount');
        $totalNetLiability = $allTransactions->sum('net_salary');
        $totalCompleted = $allTransactions->where('payment_status', 'paid')->count();
        $totalPartial = $allTransactions->where('payment_status', 'partial')->count();
        $totalCount = $allTransactions->count();

        $stats = [
            'total_disbursed' => $totalDisbursed,
            'total_liability' => $totalNetLiability,
            'total_completed' => $totalCompleted,
            'total_partial' => $totalPartial,
            'total_count' => $totalCount,
        ];

        $transactions = $query->orderBy('paid_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $filteredEmployee = $employeeId ? Employee::find($employeeId) : null;

        return view('admin.payroll.transactions', compact(
            'transactions',
            'stats',
            'selectedYear',
            'selectedMonth',
            'paymentStatus',
            'paymentMethod',
            'search',
            'employeeId',
            'filteredEmployee'
        ));
    }

    /**
     * Return single transaction statement voucher data.
     */
    public function voucher($id)
    {
        $payroll = Payroll::with(['employee.department', 'employee.designation'])->findOrFail($id);

        return response()->json([
            'voucher_no' => 'VCH-' . str_pad($payroll->id, 5, '0', STR_PAD_LEFT),
            'date' => $payroll->paid_date ? Carbon::parse($payroll->paid_date)->format('d M Y') : Carbon::parse($payroll->updated_at)->format('d M Y'),
            'employee_name' => ($payroll->employee->firstname ?? '') . ' ' . ($payroll->employee->lastname ?? ''),
            'employee_id' => $payroll->employee->unique_id ?? ('EMP-' . $payroll->employee_id),
            'department' => $payroll->employee->department->title ?? 'N/A',
            'designation' => $payroll->employee->designation->title ?? 'Staff',
            'period' => Carbon::createFromDate($payroll->year, $payroll->month, 1)->format('F Y'),
            'basic' => (float) $payroll->basic,
            'gross_salary' => (float) $payroll->gross_salary,
            'deduction' => (float) $payroll->deduction,
            'net_salary' => (float) $payroll->net_salary,
            'paid_amount' => (float) ($payroll->paid_amount ?? $payroll->net_salary),
            'balance_due' => (float) max(0, $payroll->net_salary - ($payroll->paid_amount ?? 0)),
            'payment_status' => ucfirst($payroll->payment_status ?? 'paid'),
            'payment_method' => $payroll->payment_method ?? 'Bank Transfer',
            'notes' => $payroll->notes ?? 'Salary Disbursement Statement Voucher',
        ]);
    }
}
