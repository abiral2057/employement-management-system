@extends('layouts.admin')

@section('title')
    {{ __('Manage Overtime (OT)') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-stopwatch text-dark me-2"></i>{{ __('Overtime (OT) Management') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Track, calculate, approve, and disburse employee overtime hours and extra duty pay.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#advancePaymentModal">
            <i class="fa-solid fa-hand-holding-dollar me-1"></i> {{ __('Pay in Advance / Overtime') }}
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#logOvertimeModal">
            <i class="fa-solid fa-plus me-1"></i> {{ __('Log Overtime') }}
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
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total OT Hours') }}</span>
                    <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-primary">{{ number_format($stats['total_hours'], 2) }} <small class="fs-6 fw-normal text-muted">hrs</small></h2>
                <div class="small text-muted">{{ __('Approved for selected month') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total OT Payout') }}</span>
                    <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-success">€ {{ number_format($stats['total_cost'], 2) }}</h2>
                <div class="small text-muted">{{ __('Approved overtime payroll liability') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Pending Claims') }}</span>
                    <div class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-warning">{{ $stats['pending_claims'] }}</h2>
                <div class="small text-muted">{{ __('Awaiting manager approval') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total Claims') }}</span>
                    <div class="p-2 rounded bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-info">{{ $stats['total_records'] }}</h2>
                <div class="small text-muted">{{ __('Logged entries this month') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filters ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form action="{{ route('attendance.overtime') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">{{ __('Month') }}</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Status') }}</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">{{ __('Employee') }}</label>
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeFilter == $emp->id ? 'selected' : '' }}>
                            {{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->unique_id }})
                        </option>
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

{{-- ── Overtime Table ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">
            <i class="fa-solid fa-list-check me-2 text-primary"></i>{{ __('Overtime Records') }}
        </h5>
        <span class="badge bg-light text-dark border">{{ $overtimes->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase small text-muted">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Overtime & Payout') }}</th>
                    <th class="text-end" style="width: 150px;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overtimes as $index => $ot)
                <tr>
                    <td class="text-muted fw-bold">{{ $overtimes->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-mono flex-shrink-0" style="width:42px;height:42px;background:#09090b;color:#ffffff;font-size:.9rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                {{ strtoupper(substr($ot->employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($ot->employee->lastname ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.employee.show', $ot->employee->id ?? 1) }}" class="fw-bold fs-5 text-decoration-none text-dark d-block text-hover-primary">
                                    {{ $ot->employee->firstname ?? '' }} {{ $ot->employee->lastname ?? '' }}
                                </a>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border me-1">{{ $ot->employee->unique_id ?? '—' }}</span>
                                    <span>{{ \Carbon\Carbon::parse($ot->overtime_date)->format('d M Y (D)') }}</span>
                                    @if(optional($ot->employee->department)->title)
                                        &bull; <span>{{ $ot->employee->department->title }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold text-success fs-5">€ {{ number_format($ot->amount ?? 0, 2) }}</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 fw-bold">
                                <i class="fa-regular fa-clock me-1"></i>{{ $ot->overtime_duration }} ({{ $ot->hours_decimal }}h)
                            </span>
                            <span class="text-muted small">@ €{{ number_format($ot->rate ?? 0, 2) }}/hr</span>
                            @if($ot->status === 'approved')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-circle-check me-1"></i> Approved
                                </span>
                            @elseif($ot->status === 'rejected')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1">
                                    <i class="fa-solid fa-clock me-1"></i> Pending
                                </span>
                            @endif
                        </div>
                        @if($ot->reason)
                            <div class="small text-muted mt-1 text-truncate" style="max-width: 320px;">
                                <i class="fa-regular fa-comment-dots me-1"></i>{{ $ot->reason }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                            @if($ot->status === 'pending')
                                <form action="{{ route('attendance.overtime.approve', $ot->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm px-2" title="Approve Overtime">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('attendance.overtime.reject', $ot->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning btn-sm px-2" title="Reject Overtime">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('attendance.overtime.payout', $ot->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Pay €{{ number_format($ot->amount, 2) }} overtime to {{ $ot->employee->firstname }} now?');">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm px-2 text-white fw-bold" title="Pay Overtime Now (€{{ number_format($ot->amount, 2) }})">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i>Pay
                                </button>
                            </form>
                            <form action="{{ route('attendance.overtime.destroy', $ot->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this overtime entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 opacity-25"></i>
                        <p class="mb-2">No overtime records found for this period.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#logOvertimeModal">
                            <i class="fa-solid fa-plus me-1"></i> Log Overtime
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($overtimes->hasPages())
    <div class="card-footer bg-transparent py-3">
        {{ $overtimes->links() }}
    </div>
    @endif
</div>

{{-- ── MODAL: LOG OVERTIME ── --}}
<div class="modal fade" id="logOvertimeModal" tabindex="-1" aria-labelledby="logOvertimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="logOvertimeModalLabel">
                    <i class="fa-solid fa-clock me-2"></i>{{ __('Log Employee Overtime') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('attendance.overtime.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" id="otEmployeeSelect" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" data-rate="{{ optional($emp->salary)->overtime_rate ?? 0 }}" data-basic="{{ optional($emp->salary)->basic ?? 0 }}">
                                    {{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->unique_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Overtime Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="overtime_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('OT Hours') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.25" min="0.25" max="24" name="hours" id="otHoursInput" class="form-control" value="2.0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Overtime Hourly Rate (€)') }}</label>
                        <input type="number" step="0.01" name="rate" id="otRateInput" class="form-control" placeholder="Auto-calculated from salary">
                        <div class="small text-muted mt-1">Leave empty to use employee's profile OT rate or default basic hourly rate.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Estimated Payout') }}</label>
                        <div class="p-2 bg-light rounded text-success fw-bold fs-5" id="otEstimatedPayout">
                            € 0.00
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Reason / Project Details') }}</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Month-end closing, Night server deployment, Warehouse extra loading"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> Log & Approve Overtime
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── ADVANCE SALARY / OVERTIME PAYOUT MODAL ── --}}
<div class="modal fade" id="advancePaymentModal" tabindex="-1" aria-labelledby="advancePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="advancePaymentModalLabel">
                    <i class="fa-solid fa-hand-holding-dollar me-2"></i>{{ __('Pay in Advance / Overtime Payout') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payroll.advance-payment') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border small text-muted mb-3">
                        <i class="fa-solid fa-info-circle text-success me-1"></i>
                        Disburse advance pay or overtime payment anytime before month-end. Recorded immediately against the employee's payroll statement.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select form-select-lg" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}">
                                    {{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->unique_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payment Type') }} <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="overtime_payment" selected>Overtime Payout</option>
                                <option value="advance_salary">Salary Advance</option>
                                <option value="bonus_payout">Bonus / Incentive</option>
                                <option value="interim_salary">Interim Partial Pay</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Amount (€)') }} <span class="text-danger">*</span></label>
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
                                <option value="Cash" selected>Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
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
                                    <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Payroll Year') }}</label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Voucher Notes / Reason') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Immediate overtime cash settlement">
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
<script>
function updateEstPayout() {
    const hours = parseFloat(document.getElementById('otHoursInput').value) || 0;
    const rate = parseFloat(document.getElementById('otRateInput').value) || 0;
    const total = (hours * rate).toFixed(2);
    document.getElementById('otEstimatedPayout').textContent = '€ ' + parseFloat(total).toLocaleString('en-US', {minimumFractionDigits:2});
}

document.getElementById('otEmployeeSelect')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    let rate = parseFloat(opt.getAttribute('data-rate')) || 0;
    if (rate <= 0) {
        const basic = parseFloat(opt.getAttribute('data-basic')) || 0;
        if (basic > 0) rate = (basic / (26 * 8)).toFixed(2);
    }
    if (rate > 0) {
        document.getElementById('otRateInput').value = rate;
    }
    updateEstPayout();
});

document.getElementById('otHoursInput')?.addEventListener('input', updateEstPayout);
document.getElementById('otRateInput')?.addEventListener('input', updateEstPayout);
</script>
@endsection