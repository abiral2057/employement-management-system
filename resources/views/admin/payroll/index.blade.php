@extends('layouts.admin')

@section('title')
  {{ __('Manage Payroll Sheet') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-file-invoice-dollar text-dark me-2"></i>{{ __('Monthly Payroll Sheet') }}
        </h1>
        <p class="text-muted fs-6 mb-0">{{ __('Review, adjust salary items, and save payroll entries for the workforce.') }}</p>
    </div>
    <div class="d-flex gap-3 flex-wrap">
        <a href="{{ route('payroll.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-calculator me-1"></i> {{ __('Generate / Run Payroll') }}
        </a>
        <a href="{{ route('payroll.due') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-money-bill-wave me-1"></i> {{ __('Payment Due Center') }}
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
                <label class="form-label small text-muted mb-1">{{ __('Year') }}</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">{{ __('Month') }}</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $mNum => $mName)
                        <option value="{{ $mNum }}" {{ (int)$selectedMonth == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end mt-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fa-solid fa-filter me-1"></i> {{ __('Filter Month') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Payroll Sheet Table & Form ── --}}
<form action="{{ Auth::user()->role->slug === 'super-admin' ? route('calculate.payroll') : (Auth::user()->role->slug === 'administrator' ? route('admin.calculate.payroll') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.calculate.payroll') : route('manager.calculate.payroll') )) }}" method="POST">
    @csrf
    <input type="hidden" name="year" value="{{ $selectedYear }}">
    <input type="hidden" name="month" value="{{ $selectedMonth }}">

    <div class="card border-0 shadow-sm flex-fill">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">              
            <h5 class="card-title mb-0 fw-bold">
                <i class="fa-solid fa-receipt me-2 text-primary"></i>
                {{ __('Salary Breakdown for') }} {{ DateTime::createFromFormat('!m', (int)$selectedMonth)->format('F') }} {{ $selectedYear }}
            </h5>
            <button type="submit" class="btn btn-success btn-sm fw-bold px-3">
                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save & Commit Payroll') }}
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover my-0 table-bordered align-middle table-sm text-nowrap">
                <thead class="table-light text-uppercase small text-muted">
                    <tr>
                        <th style="min-width: 180px;">{{ __('Employee') }}</th>
                        <th>{{ __('Basic (€)') }}</th>
                        <th>{{ __('House Rent') }}</th>
                        <th>{{ __('Medical') }}</th>
                        <th>{{ __('Transport') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Internet') }}</th>
                        <th>{{ __('Special') }}</th>
                        <th>{{ __('Bonus') }}</th>
                        <th>{{ __('Present') }}</th>
                        <th>{{ __('Absent') }}</th>
                        <th>{{ __('OT Pay (€)') }}</th>
                        <th class="table-success text-dark">{{ __('Gross Salary') }}</th>
                        <th>{{ __('Provident Fund') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th>{{ __('Health Ins.') }}</th>
                        <th>{{ __('Life Ins.') }}</th>
                        <th class="table-danger text-danger">{{ __('Deductions') }}</th>
                        <th class="table-primary text-primary">{{ __('Net Payable') }}</th>
                    </tr>
                </thead>
                <tbody>    
                    @foreach ($employees as $employee)
                    @php
                        $saved = $existingPayrolls->get($employee->id);
                        $sal = $employee->salary;
                        $basic = $saved ? $saved->basic : ($sal->basic ?? 0);
                        $houseRent = $saved ? $saved->house_rent : ($sal->house_rent ?? 0);
                        $medical = $saved ? $saved->medical : ($sal->medical ?? 0);
                        $transport = $saved ? $saved->transport : ($sal->transport ?? 0);
                        $phone = $saved ? $saved->phone_bill : ($sal->phone_bill ?? 0);
                        $internet = $saved ? $saved->internet_bill : ($sal->internet_bill ?? 0);
                        $special = $saved ? $saved->special : ($sal->special ?? 0);
                        $bonus = $saved ? $saved->bonus : ($sal->bonus ?? 0);
                        $otPay = $saved ? $saved->overtime_pay : ($sal->overtime_pay ?? 0);
                        $otHours = $saved ? $saved->overtime_hours : 0;
                        $pf = $saved ? $saved->provident_fund : ($sal->provident_fund ?? 0);
                        $tax = $saved ? $saved->income_tax : ($sal->income_tax ?? 0);
                        $health = $saved ? $saved->health_insurance : ($sal->health_insurance ?? 0);
                        $life = $saved ? $saved->life_insurance : ($sal->life_insurance ?? 0);

                        $present = $saved && $saved->days_present !== null ? $saved->days_present : App\Models\Attendance::where('employee_id', $employee->id)->whereYear('attendance_date', $selectedYear)->whereMonth('attendance_date', (int)$selectedMonth)->where('status', 1)->count();
                        $daysInMonth = Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth;
                        $absent = $saved && $saved->days_absent !== null ? $saved->days_absent : max(0, $daysInMonth - $present);
                    @endphp
                    <tr class="employee-row">
                        <td>
                            <strong class="text-dark">{{ $employee->firstname }} {{ $employee->lastname }}</strong>
                            <div class="small text-muted">{{ $employee->unique_id }} &bull; {{ optional($employee->designation)->title ?? 'Staff' }}</div>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][basic]" class="form-control form-control-sm basic" value="{{ $basic }}" style="width: 95px;" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][house_rent]" class="form-control form-control-sm rent" value="{{ $houseRent }}" style="width: 85px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][medical]" class="form-control form-control-sm medical" value="{{ $medical }}" style="width: 85px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][transport]" class="form-control form-control-sm transport" value="{{ $transport }}" style="width: 85px;">
                        </td>  
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][phone_bill]" class="form-control form-control-sm mobile" value="{{ $phone }}" style="width: 80px;">
                        </td>  
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][internet_bill]" class="form-control form-control-sm internet" value="{{ $internet }}" style="width: 80px;">
                        </td>  
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][special]" class="form-control form-control-sm special" value="{{ $special }}" style="width: 85px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][bonus]" class="form-control form-control-sm bonus" value="{{ $bonus }}" style="width: 85px;">
                        </td>
                        <td>
                            <input type="number" name="payroll[{{ $employee->id }}][days_present]" class="form-control form-control-sm present" value="{{ $present }}" style="width: 65px;">
                        </td>
                        <td>
                            <input type="number" name="payroll[{{ $employee->id }}][days_absent]" class="form-control form-control-sm absent" value="{{ $absent }}" style="width: 65px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][overtime_pay]" class="form-control form-control-sm otpay" value="{{ $otPay }}" style="width: 85px;">
                            <input type="hidden" name="payroll[{{ $employee->id }}][overtime_hours]" value="{{ $otHours }}">
                        </td>
                        <td class="table-success">
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][gross_salary]" class="form-control form-control-sm gross fw-bold bg-transparent border-0" readonly style="width: 105px;">
                        </td>  
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][provident_fund]" class="form-control form-control-sm pf" value="{{ $pf }}" style="width: 85px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][income_tax]" class="form-control form-control-sm tax" value="{{ $tax }}" style="width: 85px;">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][health_insurance]" class="form-control form-control-sm health" value="{{ $health }}" style="width: 85px;">
                        </td> 
                        <td>
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][life_insurance]" class="form-control form-control-sm life" value="{{ $life }}" style="width: 85px;">
                        </td> 
                        <td class="table-danger">
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][deduction]" class="form-control form-control-sm deduct fw-bold text-danger bg-transparent border-0" readonly style="width: 95px;">
                        </td>
                        <td class="table-primary">
                            <input type="number" step="0.01" name="payroll[{{ $employee->id }}][net_salary]" class="form-control form-control-sm net fw-bold text-primary bg-transparent border-0 fs-6" readonly style="width: 110px;">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent py-3 d-flex justify-content-between align-items-center">
            <span class="text-muted small">Real-time calculations automatically recalculate Gross, Deductions, and Net salary on change.</span>
            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save & Commit Payroll') }}
            </button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
function calcRow($row) {
    const basic = parseFloat($row.find(".basic").val()) || 0;
    const rent = parseFloat($row.find(".rent").val()) || 0;
    const medical = parseFloat($row.find(".medical").val()) || 0;
    const transport = parseFloat($row.find(".transport").val()) || 0;
    const mobile = parseFloat($row.find(".mobile").val()) || 0;
    const internet = parseFloat($row.find(".internet").val()) || 0;
    const special = parseFloat($row.find(".special").val()) || 0;
    const bonus = parseFloat($row.find(".bonus").val()) || 0;
    const otpay = parseFloat($row.find(".otpay").val()) || 0;

    const pf = parseFloat($row.find(".pf").val()) || 0;
    const tax = parseFloat($row.find(".tax").val()) || 0;
    const health = parseFloat($row.find(".health").val()) || 0;
    const life = parseFloat($row.find(".life").val()) || 0;

    const gross = basic + rent + medical + transport + mobile + internet + special + bonus + otpay;
    const deductions = pf + tax + health + life;
    const net = Math.max(0, gross - deductions);

    $row.find(".gross").val(gross.toFixed(2));
    $row.find(".deduct").val(deductions.toFixed(2));
    $row.find(".net").val(net.toFixed(2));
}

$(document).ready(function() {
    $(".employee-row").each(function() {
        calcRow($(this));
    });

    $(".employee-row input").on("input", function() {
        calcRow($(this).closest(".employee-row"));
    });
});
</script>
@endsection