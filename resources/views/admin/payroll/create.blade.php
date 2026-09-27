@extends('layouts.admin')

@section('title')
    {{ __('Generate Monthly Payroll') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-file-invoice-dollar text-dark me-2"></i>{{ __('Generate Monthly Payroll') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Preview, calculate, and commit monthly salaries including attendance days, approved Overtime, and Extra Shift allowances.
        </p>
    </div>
    <div class="d-flex gap-3 flex-wrap">
        <a href="{{ route('payroll.due') }}" class="btn btn-outline-danger">
            <i class="fa-solid fa-bell me-1"></i> {{ __('Payment Due Center') }}
        </a>
        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Payroll') }}
        </a>
    </div>
</div>
@endsection

@section('content')
{{-- ── Period Selector ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">{{ __('Payroll Year') }}</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">{{ __('Payroll Month') }}</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ (int)$selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end mt-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fa-solid fa-arrows-rotate me-1"></i> {{ __('Refresh Calculation') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Calculation Table & Commit Form ── --}}
<form action="{{ Auth::user()->role->slug === 'super-admin' ? route('calculate.payroll') : (Auth::user()->role->slug === 'administrator' ? route('admin.calculate.payroll') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.calculate.payroll') : route('manager.calculate.payroll') )) }}" method="POST">
    @csrf
    <input type="hidden" name="year" value="{{ $selectedYear }}">
    <input type="hidden" name="month" value="{{ $selectedMonth }}">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fa-solid fa-calculator me-2 text-primary"></i>
                    {{ __('Payroll Calculation Preview for') }} {{ DateTime::createFromFormat('!m', (int)$selectedMonth)->format('F') }} {{ $selectedYear }}
                </h5>
                <small class="text-muted">{{ $daysInMonth }} days in month &bull; Overtime & extra duty included</small>
            </div>
            <button type="submit" class="btn btn-success btn-sm fw-bold px-3">
                <i class="fa-solid fa-check-double me-1"></i> {{ __('Commit & Generate Payroll') }}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm text-nowrap">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Days (P / A)') }}</th>
                        <th>{{ __('Basic (€)') }}</th>
                        <th>{{ __('OT Pay (€)') }}</th>
                        <th>{{ __('Allowances (€)') }}</th>
                        <th>{{ __('Gross Salary (€)') }}</th>
                        <th>{{ __('Total Deductions (€)') }}</th>
                        <th>{{ __('Net Payable (€)') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandGross = 0; $grandDeduct = 0; $grandNet = 0; @endphp
                    @forelse($employees as $index => $item)
                    @php
                        $emp = $item['employee'];
                        $grandGross += $item['gross'];
                        $grandDeduct += $item['deductions'];
                        $grandNet += $item['net'];
                        $totalAllowances = $item['house_rent'] + $item['medical'] + $item['transport'] + $item['phone_bill'] + $item['internet_bill'] + $item['special'] + $item['bonus'] + $item['extra_shift_allowance'];
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-mono" style="width:32px;height:32px;background:#09090b;color:#ffffff;font-size:.75rem;border-radius:50%;">
                                    {{ strtoupper(substr($emp->firstname, 0, 1)) }}{{ strtoupper(substr($emp->lastname, 0, 1)) }}
                                </div>
                                <div>
                                    <strong class="text-dark">{{ $emp->firstname }} {{ $emp->lastname }}</strong>
                                    <div class="small text-muted">{{ $emp->unique_id }} &bull; {{ optional($emp->designation)->title ?? 'Staff' }}</div>
                                </div>
                            </div>
                            {{-- Hidden inputs for payroll calculation --}}
                            <input type="hidden" name="payroll[{{ $emp->id }}][basic]" value="{{ $item['basic'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][house_rent]" value="{{ $item['house_rent'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][medical]" value="{{ $item['medical'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][transport]" value="{{ $item['transport'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][phone_bill]" value="{{ $item['phone_bill'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][internet_bill]" value="{{ $item['internet_bill'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][special]" value="{{ $item['special'] + $item['extra_shift_allowance'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][bonus]" value="{{ $item['bonus'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][overtime_hours]" value="{{ $item['ot_hours'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][overtime_pay]" value="{{ $item['ot_pay'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][days_present]" value="{{ $item['present'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][days_absent]" value="{{ $item['absent'] }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][provident_fund]" value="{{ optional($emp->salary)->provident_fund ?? 0 }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][income_tax]" value="{{ optional($emp->salary)->income_tax ?? 0 }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][health_insurance]" value="{{ optional($emp->salary)->health_insurance ?? 0 }}">
                            <input type="hidden" name="payroll[{{ $emp->id }}][life_insurance]" value="{{ optional($emp->salary)->life_insurance ?? 0 }}">
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">{{ $item['present'] }} P</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger ms-1">{{ $item['absent'] }} A</span>
                        </td>
                        <td>
                            <span class="fw-semibold">€ {{ number_format($item['basic'], 2) }}</span>
                        </td>
                        <td>
                            @if($item['ot_pay'] > 0)
                                <span class="text-primary fw-semibold">+ € {{ number_format($item['ot_pay'], 2) }}</span>
                                <div class="small text-muted">({{ $item['ot_hours'] }} hrs)</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span>€ {{ number_format($totalAllowances, 2) }}</span>
                            @if($item['extra_shift_allowance'] > 0)
                                <div class="small text-warning fw-semibold"><i class="fa-solid fa-bolt me-1"></i>+€{{ number_format($item['extra_shift_allowance'], 2) }} extra shift</div>
                            @endif
                        </td>
                        <td>
                            <strong class="text-dark">€ {{ number_format($item['gross'], 2) }}</strong>
                        </td>
                        <td>
                            <span class="text-danger">− € {{ number_format($item['deductions'], 2) }}</span>
                        </td>
                        <td>
                            <strong class="text-success fs-6">€ {{ number_format($item['net'], 2) }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">No active employees found.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="6" class="text-end text-uppercase">{{ __('Total Summary Liability') }}:</td>
                        <td class="text-dark">€ {{ number_format($grandGross, 2) }}</td>
                        <td class="text-danger">− € {{ number_format($grandDeduct, 2) }}</td>
                        <td class="text-success fs-5">€ {{ number_format($grandNet, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer bg-transparent py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">Committing will calculate payment due dates and prepare payslips.</span>
            <button type="submit" class="btn btn-primary fw-bold px-4 py-2">
                <i class="fa-solid fa-check me-1"></i> {{ __('Commit & Generate Payroll') }}
            </button>
        </div>
    </div>
</form>
@endsection
