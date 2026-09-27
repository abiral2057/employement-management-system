@extends('layouts.admin')

@section('title')
    {{ __('Transaction History & Payment Statements') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-receipt text-dark me-2"></i>{{ __('Transaction History & Statements') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Comprehensive audit ledger of all completed and partial employee salary disbursements and statement vouchers.
        </p>
    </div>
    <div class="d-flex gap-3 flex-wrap">
        <a href="{{ route('payroll.due') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ __('Payment Due Center') }}
        </a>
        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-calculator me-1"></i> {{ __('Payroll Sheet') }}
        </a>
        <button type="button" onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print me-1"></i> {{ __('Print All Statements') }}
        </button>
    </div>
</div>
@endsection

@section('content')
{{-- ── Summary KPI Cards ── --}}
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-xl-3">
        <div class="card border shadow-sm h-100 highlight-module bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Total Disbursed') }}</span>
                    <div class="p-2 rounded bg-light text-dark">
                        <i class="fa-solid fa-wallet fs-5"></i>
                    </div>
                </div>
                <h2 class="h2 mb-1 fw-bold text-dark">€ {{ number_format($stats['total_disbursed'], 2) }}</h2>
                <div class="small text-muted">{{ __('Across :count transactions', ['count' => $stats['total_count']]) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border shadow-sm h-100 highlight-module bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Net Liability') }}</span>
                    <div class="p-2 rounded bg-light text-dark">
                        <i class="fa-solid fa-file-invoice fs-5"></i>
                    </div>
                </div>
                <h2 class="h2 mb-1 fw-bold text-dark">€ {{ number_format($stats['total_liability'], 2) }}</h2>
                <div class="small text-muted">{{ __('Total Net Salary Obligation') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border shadow-sm h-100 highlight-module bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Completed Payouts') }}</span>
                    <div class="p-2 rounded bg-light text-success">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                    </div>
                </div>
                <h2 class="h2 mb-1 fw-bold text-dark">{{ $stats['total_completed'] }}</h2>
                <div class="small text-muted"><span class="badge bg-success bg-opacity-10 text-success fw-bold">100% Cleared</span> transactions</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border shadow-sm h-100 highlight-module bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-bold">{{ __('Partial Payouts') }}</span>
                    <div class="p-2 rounded bg-light text-warning">
                        <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                    </div>
                </div>
                <h2 class="h2 mb-1 fw-bold text-dark">{{ $stats['total_partial'] }}</h2>
                <div class="small text-muted"><span class="badge bg-warning bg-opacity-10 text-dark fw-bold">Balance pending</span> payouts</div>
            </div>
        </div>
    </div>
</div>

@if(isset($filteredEmployee) && $filteredEmployee)
@php
    $empShowUrl = '#';
    if (Auth::check() && Auth::user()->role) {
        $userRoleSlug = Auth::user()->role->slug;
        if ($userRoleSlug === 'super-admin') {
            $empShowUrl = route('employee.show', $filteredEmployee->id);
        } elseif ($userRoleSlug === 'administrator') {
            $empShowUrl = route('admin.employee.show', $filteredEmployee->id);
        } else {
            $empShowUrl = route('hr.employee.show', $filteredEmployee->id);
        }
    }
@endphp
<div class="alert alert-light border border-dark d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="avatar-mono flex-shrink-0" style="width:44px;height:44px;background:#09090b;color:#ffffff;font-size:1rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
            {{ strtoupper(substr($filteredEmployee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($filteredEmployee->lastname ?? 'M', 0, 1)) }}
        </div>
        <div>
            <h5 class="mb-0 fw-bold">{{ $filteredEmployee->firstname }} {{ $filteredEmployee->lastname }} — {{ __('Personal Transaction Ledger') }}</h5>
            <div class="small text-muted">
                <span class="badge bg-light text-dark border me-1">{{ $filteredEmployee->unique_id }}</span>
                <span>{{ optional($filteredEmployee->department)->title ?? 'General' }}</span> &bull; <span>{{ optional($filteredEmployee->designation)->title ?? 'Staff' }}</span>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ $empShowUrl }}" class="btn btn-outline-dark btn-sm fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Profile') }}
        </a>
        <a href="{{ route('payroll.transactions') }}" class="btn btn-dark btn-sm fw-bold">
            <i class="fa-solid fa-users me-1"></i> {{ __('All Transactions') }}
        </a>
    </div>
</div>
@endif

{{-- ── Filters & Search ── --}}
<div class="card border shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ route('payroll.transactions') }}" method="GET" class="row g-3 align-items-end">
            @if(isset($employeeId) && $employeeId)
                <input type="hidden" name="employee_id" value="{{ $employeeId }}">
            @endif
            <div class="col-md-3">
                <label class="form-label small text-muted">{{ __('Search Employee') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, ID, email..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">{{ __('Year') }}</label>
                <select name="year" class="form-select">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">{{ __('Month') }}</label>
                <select name="month" class="form-select">
                    <option value="">All Months</option>
                    @php $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December']; @endphp
                    @foreach($months as $num => $mName)
                        <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">{{ __('Payment Method') }}</label>
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="Bank Transfer" {{ $paymentMethod === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Cash" {{ $paymentMethod === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Cheque" {{ $paymentMethod === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="Mobile Banking" {{ $paymentMethod === 'Mobile Banking' ? 'selected' : '' }}>Mobile Banking</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">{{ __('Status') }}</label>
                <select name="payment_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="partial" {{ $paymentStatus === 'partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100" title="Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if($search || $selectedMonth || $paymentStatus || $paymentMethod || $employeeId)
                <a href="{{ route('payroll.transactions') }}" class="btn btn-outline-secondary" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ── Transactions Ledger Table ── --}}
<div class="card border shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-white p-4 border-bottom">
        <div>
            <h5 class="card-title mb-1 fw-bold text-dark">{{ __('Disbursement Transaction Ledger') }}</h5>
            <span class="text-muted small">Showing {{ $transactions->total() }} recorded transaction statements</span>
        </div>
        <span class="badge badge-yellowgreen">{{ $transactions->total() }} Records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th scope="col" style="width: 160px;">{{ __('Voucher & Period') }}</th>
                    <th scope="col">{{ __('Employee') }}</th>
                    <th scope="col">{{ __('Net Disbursed & Method') }}</th>
                    <th scope="col" class="text-end" style="width: 150px;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                @php
                    $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
                    $periodStr = ($months[$txn->month] ?? $txn->month) . ' ' . $txn->year;
                    $voucherNo = 'VCH-' . str_pad($txn->id, 5, '0', STR_PAD_LEFT);
                    $empShowUrl = Auth::user()->role->slug === 'super-admin' 
                        ? route('employee.show', $txn->employee_id) 
                        : (Auth::user()->role->slug === 'administrator' 
                            ? route('admin.employee.show', $txn->employee_id) 
                            : route('hr.employee.show', $txn->employee_id));
                @endphp
                <tr>
                    <td class="py-3">
                        <span class="font-monospace fw-bold text-dark bg-light px-2 py-1 rounded border small d-inline-block mb-1">{{ $voucherNo }}</span>
                        <div class="fw-bold text-dark fs-6">{{ $periodStr }}</div>
                        <div class="text-muted small">{{ $txn->paid_date ? \Carbon\Carbon::parse($txn->paid_date)->format('d M Y') : \Carbon\Carbon::parse($txn->updated_at)->format('d M Y') }}</div>
                    </td>
                    <td class="py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-mono flex-shrink-0" style="width:44px;height:44px;background:#09090b;color:#ffffff;font-size:1rem;border-radius:50%;">
                                {{ strtoupper(substr($txn->employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($txn->employee->lastname ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ $empShowUrl }}" class="fw-bold fs-5 text-dark text-decoration-none d-block" style="letter-spacing: -0.02em;">
                                    {{ $txn->employee->firstname ?? '' }} {{ $txn->employee->lastname ?? '' }}
                                </a>
                                <span class="text-muted small">{{ $txn->employee->unique_id ?? '—' }} &bull; {{ optional($txn->employee->department)->title ?? 'Staff' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="fw-bold fs-4 text-dark mb-1 font-tabular" style="letter-spacing: -0.02em;">
                            € {{ number_format($txn->paid_amount ?? $txn->net_salary, 2) }}
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border">
                                <i class="fa-solid fa-credit-card me-1 text-muted"></i>{{ $txn->payment_method ?? 'Bank Transfer' }}
                            </span>
                            @if($txn->payment_status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-check me-1"></i> Paid
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Partial
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end py-3">
                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" onclick="openVoucherModal({{ $txn->id }})" title="View Statement Voucher">
                            <i class="fa-solid fa-receipt me-1"></i> Voucher
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-receipt fs-1 text-secondary mb-3 d-block"></i>
                        <h5>{{ __('No Payment Transactions Found') }}</h5>
                        <p class="small mb-0">{{ __('No completed or partial salary disbursements match the filter criteria.') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer bg-white p-4 border-top">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

{{-- ── Printable Voucher Statement Modal ── --}}
<div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="brand-graphic" style="width: 32px; height: 32px; background: #9acd32; color: #09090b; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </span>
                    <div>
                        <h5 class="modal-title mb-0 text-white fw-bold" id="voucherModalLabel">{{ __('Disbursement Statement Voucher') }}</h5>
                        <span class="small text-muted" id="vchNumber">VCH-00000</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="voucherPrintArea">
                <div class="border rounded p-4 bg-light mb-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small text-uppercase d-block fw-bold">Employee Name</span>
                            <strong class="fs-6 text-dark" id="vchEmpName">—</strong>
                            <div class="small text-muted" id="vchEmpId">—</div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="text-muted small text-uppercase d-block fw-bold">Department & Role</span>
                            <strong class="text-dark" id="vchEmpDept">—</strong>
                            <div class="small text-muted" id="vchEmpDesig">—</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div class="p-3 bg-white border rounded">
                            <span class="text-muted small d-block">Pay Period</span>
                            <strong class="text-dark fs-6" id="vchPeriod">—</strong>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-white border rounded">
                            <span class="text-muted small d-block">Disbursement Date</span>
                            <strong class="text-dark fs-6" id="vchDate">—</strong>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-white border rounded">
                            <span class="text-muted small d-block">Payment Method</span>
                            <strong class="text-dark fs-6" id="vchMethod">—</strong>
                        </div>
                    </div>
                </div>

                <div class="card border mb-4">
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted py-2 ps-3">Gross Salary Payable</td>
                                    <td class="text-end fw-semibold py-2 pe-3" id="vchGross">€ 0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-2 ps-3">Total Deductions (Tax, PF, Leaves)</td>
                                    <td class="text-end text-danger fw-semibold py-2 pe-3" id="vchDeduct">− € 0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-dark fw-bold py-2 ps-3">Net Salary Liability</td>
                                    <td class="text-end fw-bold py-2 pe-3 text-dark" id="vchNet">€ 0.00</td>
                                </tr>
                                <tr class="table-light border-top">
                                    <td class="fw-bold py-3 ps-3 text-dark fs-6">Net Disbursed Amount</td>
                                    <td class="text-end fw-bold py-3 pe-3 text-dark fs-5 font-tabular" id="vchPaid">€ 0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-3 border rounded bg-white mb-4">
                    <span class="text-muted small d-block fw-bold mb-1">Transaction Remarks / References</span>
                    <p class="small text-dark mb-0" id="vchNotes">—</p>
                </div>

                <div class="row pt-4 text-center mt-4 border-top">
                    <div class="col-6">
                        <div class="border-top pt-2 mx-auto" style="width: 180px;">
                            <span class="small text-muted">Authorized Signature</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border-top pt-2 mx-auto" style="width: 180px;">
                            <span class="small text-muted">Recipient Acknowledgment</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-3 bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printVoucher()">
                    <i class="fa-solid fa-print me-1"></i> Print Statement Voucher
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function openVoucherModal(txnId) {
    fetch(`/payroll/transactions/${txnId}/voucher`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('vchNumber').textContent = data.voucher_no;
            document.getElementById('vchEmpName').textContent = data.employee_name;
            document.getElementById('vchEmpId').textContent = data.employee_id;
            document.getElementById('vchEmpDept').textContent = data.department;
            document.getElementById('vchEmpDesig').textContent = data.designation;
            document.getElementById('vchPeriod').textContent = data.period;
            document.getElementById('vchDate').textContent = data.date;
            document.getElementById('vchMethod').textContent = data.payment_method;
            document.getElementById('vchGross').textContent = '€ ' + Number(data.gross_salary).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('vchDeduct').textContent = '− € ' + Number(data.deduction).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('vchNet').textContent = '€ ' + Number(data.net_salary).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('vchPaid').textContent = '€ ' + Number(data.paid_amount).toLocaleString(undefined, {minimumFractionDigits: 2});
            document.getElementById('vchNotes').textContent = data.notes || 'Salary disbursement processed successfully.';

            const modal = new bootstrap.Modal(document.getElementById('voucherModal'));
            modal.show();
        })
        .catch(err => {
            alert('Failed to load transaction statement voucher.');
        });
}

function printVoucher() {
    const content = document.getElementById('voucherPrintArea').innerHTML;
    const printWindow = window.open('', '', 'width=800,height=900');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Transaction Statement Voucher</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 30px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
                @media print { body { padding: 0; } }
            </style>
        </head>
        <body onload="window.print();window.close();">
            <div class="text-center mb-4 pb-3 border-bottom">
                <h3 class="fw-bold mb-1">HRMS ENTERPRISE</h3>
                <p class="text-muted small mb-0">Official Salary Disbursement & Transaction Statement Voucher</p>
            </div>
            ${content}
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>
@endsection
