@extends('layouts.admin')

@section('title')
    {{ __('Payment Due & Payment Reminder Center') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-file-invoice-dollar text-dark me-2"></i>{{ __('Payment Due & Reminder Center') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Track unpaid and partial salaries, monitor overdue balances, record payments, and dispatch payment reminders.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#advancePaymentModal">
            <i class="fa-solid fa-hand-holding-dollar me-1"></i> {{ __('Pay in Advance / Add Salary') }}
        </button>
        @if($stats['overdue_count'] > 0)
        <form action="{{ route('payroll.due.batch-reminder') }}" method="POST" class="d-inline" onsubmit="return confirm('Send automated reminders to all {{ $stats['overdue_count'] }} overdue employees?');">
            @csrf
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <button type="submit" class="btn btn-danger">
                <i class="fa-solid fa-bell me-1"></i> {{ __('Send Overdue Reminders') }} ({{ $stats['overdue_count'] }})
            </button>
        </form>
        @endif
        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-table-list me-1"></i> {{ __('Payroll Sheet') }}
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print me-1"></i> {{ __('Print Report') }}
        </button>
    </div>
</div>
@endsection

@section('content')
{{-- ── KPI Cards ── --}}
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total Outstanding Due') }}</span>
                    <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-danger">€ {{ number_format($stats['total_outstanding'], 2) }}</h2>
                <div class="small text-muted">
                    <span class="badge bg-danger bg-opacity-10 text-danger">{{ $stats['unpaid_count'] }} unpaid</span> &bull;
                    <span class="badge bg-info bg-opacity-10 text-info">{{ $stats['partial_count'] }} partial</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Overdue Payments') }}</span>
                    <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-calendar-xmark"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-danger">{{ $stats['overdue_count'] }}</h2>
                <div class="small text-muted">{{ __('Past due date requiring immediate notice') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total Disbursed (Paid)') }}</span>
                    <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-success">€ {{ number_format($stats['total_paid'], 2) }}</h2>
                @php
                    $pct = $stats['total_liability'] > 0 ? round(($stats['total_paid'] / $stats['total_liability']) * 100) : 0;
                @endphp
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="small text-muted mt-1">{{ $pct }}% of € {{ number_format($stats['total_liability'], 2) }} settled ({{ $stats['paid_count'] }} paid)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total Payroll Liability') }}</span>
                    <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-primary">€ {{ number_format($stats['total_liability'], 2) }}</h2>
                <div class="small text-muted">{{ $stats['total_employees'] }} total processed records</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter Bar ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form action="{{ route('payroll.due') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">{{ __('Year') }}</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Month') }}</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ (int)$selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Payment Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="overdue" {{ $statusFilter === 'overdue' ? 'selected' : '' }}>🚨 Overdue Payments</option>
                    <option value="unpaid" {{ $statusFilter === 'unpaid' ? 'selected' : '' }}>⏳ Unpaid (0% Paid)</option>
                    <option value="partial" {{ $statusFilter === 'partial' ? 'selected' : '' }}>💳 Partially Paid</option>
                    <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>✅ Fully Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Department') }}</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentFilter == $dept->id ? 'selected' : '' }}>{{ $dept->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end mt-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Due Table ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">
            <i class="fa-solid fa-money-check-dollar me-2 text-primary"></i>{{ __('Employee Salary Due & Settlement') }}
        </h5>
        <span class="badge bg-light text-dark border">{{ $payrolls->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase small text-muted">
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>{{ __('Employee & Period') }}</th>
                    <th>{{ __('Salary & Outstanding Balance') }}</th>
                    <th class="text-end" style="width: 200px;">{{ __('Status & Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $index => $payroll)
                @php
                    $isOverdue = $payroll->isOverdue();
                    $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                    $periodStr = ($months[$payroll->month] ?? $payroll->month) . ' ' . $payroll->year;
                    $empShowUrl = Auth::user()->role->slug === 'super-admin' 
                        ? route('employee.show', $payroll->employee_id) 
                        : (Auth::user()->role->slug === 'administrator' 
                            ? route('admin.employee.show', $payroll->employee_id) 
                            : route('hr.employee.show', $payroll->employee_id));
                @endphp
                <tr class="{{ $isOverdue ? 'table-danger table-opacity-10' : '' }}">
                    <td class="text-muted fw-bold">{{ $payrolls->firstItem() + $index }}</td>
                    <td class="py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-mono flex-shrink-0" style="width:44px;height:44px;background:#09090b;color:#ffffff;font-size:1rem;border-radius:50%;">
                                {{ strtoupper(substr($payroll->employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($payroll->employee->lastname ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ $empShowUrl }}" class="fw-bold fs-5 text-dark text-decoration-none d-block" style="letter-spacing: -0.02em;">
                                    {{ $payroll->employee->firstname ?? '' }} {{ $payroll->employee->lastname ?? '' }}
                                </a>
                                <div class="text-muted small mt-1">
                                    <span class="badge bg-light text-dark border">{{ $periodStr }}</span>
                                    <span class="ms-1">{{ $payroll->employee->unique_id ?? '—' }} &bull; {{ optional($payroll->employee->department)->title ?? 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        @if($payroll->balance_due > 0)
                            <div class="fw-bold fs-4 text-danger mb-0" style="letter-spacing: -0.02em;">
                                € {{ number_format($payroll->balance_due, 2) }} <span class="small fs-6 fw-normal text-muted">Due</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Total: <strong>€ {{ number_format($payroll->net_salary, 2) }}</strong> &bull; Paid: <span class="text-success">€ {{ number_format($payroll->paid_amount, 2) }}</span>
                                @if($payroll->due_date)
                                    &bull; Due {{ \Carbon\Carbon::parse($payroll->due_date)->format('d M') }}
                                @endif
                            </div>
                        @else
                            <div class="fw-bold fs-4 text-success mb-0">
                                € {{ number_format($payroll->net_salary, 2) }}
                            </div>
                            <div class="text-muted small mt-1">
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-check me-1"></i>Fully Settled</span>
                            </div>
                        @endif
                    </td>
                    <td class="text-end py-3">
                        <div class="d-inline-flex flex-column align-items-end gap-1">
                            <div>
                                @if($payroll->payment_status === 'paid')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Paid
                                    </span>
                                @elseif($isOverdue)
                                    <span class="badge bg-danger text-white px-2 py-1">
                                        <i class="fa-solid fa-bell me-1"></i> Overdue
                                    </span>
                                @elseif($payroll->payment_status === 'partial')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                        <i class="fa-solid fa-credit-card me-1"></i> Partial
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1">
                                        <i class="fa-solid fa-clock me-1"></i> Unpaid
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                @if($payroll->balance_due > 0)
                                    <button type="button" class="btn btn-dark btn-sm rounded-pill px-3 record-pay-btn"
                                        data-bs-toggle="modal" data-bs-target="#recordPaymentModal"
                                        data-id="{{ $payroll->id }}"
                                        data-name="{{ $payroll->employee->firstname }} {{ $payroll->employee->lastname }}"
                                        data-emp-id="{{ $payroll->employee->unique_id }}"
                                        data-period="{{ $periodStr }}"
                                        data-net="{{ $payroll->net_salary }}"
                                        data-paid="{{ $payroll->paid_amount }}"
                                        data-due="{{ $payroll->balance_due }}">
                                        <i class="fa-solid fa-money-bill-transfer me-1"></i> Pay
                                    </button>
                                    <form action="{{ route('payroll.due.send-reminder', $payroll->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="Send Notice">
                                            <i class="fa-solid fa-bell"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="btn btn-light btn-sm text-success disabled rounded-pill px-3">
                                        <i class="fa-solid fa-check-double me-1"></i> Settled
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clipboard-check fa-3x mb-3 text-success opacity-50"></i>
                        <p class="mb-2">No payroll records matching the selected month and status filter.</p>
                        <a href="{{ route('payroll.index') }}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-calculator me-1"></i> Generate / View Payroll
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payrolls->hasPages())
    <div class="card-footer bg-transparent py-3">
        {{ $payrolls->links() }}
    </div>
    @endif
</div>

{{-- ── MODAL: RECORD PAYMENT ── --}}
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="recordPaymentModalLabel">
                    <i class="fa-solid fa-money-bill-wave me-2"></i>{{ __('Record Salary Payment') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recordPaymentForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="p-3 bg-light rounded mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Employee:</span>
                            <strong id="modalEmpName" class="text-dark">—</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Payroll Period:</span>
                            <span id="modalPeriod" class="fw-semibold text-dark">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Net Payable:</span>
                            <span id="modalNetSalary" class="fw-semibold">€ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Already Paid:</span>
                            <span id="modalAlreadyPaid" class="text-success fw-semibold">€ 0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="text-danger">Current Balance Due:</strong>
                            <strong id="modalBalanceDue" class="text-danger fs-5">€ 0.00</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Amount Paying Now (€)') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">€</span>
                            <input type="number" step="0.01" name="amount_paying" id="modalAmountPaying" class="form-control fw-bold fs-5 text-success" required>
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="payFullBtn">Pay Full Due</button>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Bank Transfer">Bank Transfer (EFT/ACH)</option>
                                <option value="Cash">Cash Voucher</option>
                                <option value="Check">Check Payment</option>
                                <option value="Mobile Banking">bKash / Nagad / Rocket</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="paid_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Transaction Ref / Voucher Note') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Bank Ref #TXN-98402, Voucher #420">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Confirm & Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── ADVANCE / EARLY SALARY / OVERTIME PAYOUT MODAL ── --}}
<div class="modal fade" id="advancePaymentModal" tabindex="-1" aria-labelledby="advancePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="advancePaymentModalLabel">
                    <i class="fa-solid fa-hand-holding-dollar me-2"></i>{{ __('Disburse Advance / Early Salary / Overtime') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payroll.advance-payment') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="fa-solid fa-info-circle text-success me-1"></i>
                        Disburse advance payments, mid-month salary, or overtime anytime before month-end. It will immediately credit the employee's payroll and reduce outstanding liability.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select form-select-lg" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($allEmployees ?? [] as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->unique_id }}) - {{ optional($emp->department)->title ?? 'General' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="advance_salary">Salary Advance</option>
                                <option value="overtime_payment">Overtime Payout</option>
                                <option value="bonus_payout">Bonus / Incentive</option>
                                <option value="interim_salary">Interim Partial Pay</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Disbursement Amount (€)') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">€</span>
                                <input type="number" step="0.01" min="1" name="amount" class="form-control fw-bold fs-5 text-success" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payment Method') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash" selected>Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Direct Deposit">Direct Deposit</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="paid_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payroll Month') }}</label>
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $selectedMonth ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payroll Year') }}</label>
                            <input type="number" name="year" class="form-control" value="{{ $selectedYear }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Voucher Notes / Reason') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Mid-month advance for personal emergency, or Weekend OT payment">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Disburse & Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<style>
.pulse-badge {
    animation: pulseRed 2s infinite;
}
@keyframes pulseRed {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.6); }
    70% { box-shadow: 0 0 0 6px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
</style>
<script>
document.querySelectorAll('.record-pay-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const empId = this.getAttribute('data-emp-id');
        const period = this.getAttribute('data-period');
        const net = parseFloat(this.getAttribute('data-net')) || 0;
        const paid = parseFloat(this.getAttribute('data-paid')) || 0;
        const due = parseFloat(this.getAttribute('data-due')) || 0;

        document.getElementById('modalEmpName').textContent = name + ' (' + empId + ')';
        document.getElementById('modalPeriod').textContent = period;
        document.getElementById('modalNetSalary').textContent = '€ ' + net.toLocaleString('en-US', {minimumFractionDigits:2});
        document.getElementById('modalAlreadyPaid').textContent = '€ ' + paid.toLocaleString('en-US', {minimumFractionDigits:2});
        document.getElementById('modalBalanceDue').textContent = '€ ' + due.toLocaleString('en-US', {minimumFractionDigits:2});

        const payInput = document.getElementById('modalAmountPaying');
        payInput.value = due.toFixed(2);
        payInput.max = due.toFixed(2);

        document.getElementById('payFullBtn').onclick = () => { payInput.value = due.toFixed(2); };

        const form = document.getElementById('recordPaymentForm');
        form.action = '/payroll/due/' + id + '/pay';
    });
});
</script>
@endsection
