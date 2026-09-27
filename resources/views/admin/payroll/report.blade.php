@extends('layouts.admin')

@section('title')
  {{ __('Payroll Sheet Report') }}
@endsection

@section('header')
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h2 fw-bold text-dark mb-1">
        <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>{{ __('Payroll Sheet Report') }}
      </h1>
      <p class="text-secondary small mb-0">{{ __('Generate monthly attendance summary, review salary structures, and lock/export statements') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('payroll.export.csv', ['year' => $selectedYear ?? date('Y'), 'month' => $selectedMonth ?? date('m')]) }}" class="btn btn-outline-success fw-semibold shadow-sm">
        <i class="fa-solid fa-file-csv me-1"></i> {{ __('Export CSV') }}
      </a>
      <a href="{{ route('payroll.transactions') }}" class="btn btn-outline-dark fw-semibold">
        <i class="fa-solid fa-receipt me-1"></i> {{ __('Disbursements') }}
      </a>
      <a href="{{ route('payroll.due') }}" class="btn btn-outline-warning text-dark fw-semibold">
        <i class="fa-solid fa-clock-rotate-left me-1"></i> {{ __('Payment Due Center') }}
      </a>
      <a href="{{ route('payroll.create') }}" class="btn btn-primary fw-semibold shadow-sm">
        <i class="fa-solid fa-calculator me-1"></i> {{ __('Generate Payroll') }}
      </a>
    </div>
  </div>
@endsection

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
      <div class="card-header bg-white border-bottom py-3">              
        <h5 class="card-title fw-bold text-dark mb-0"><i class="fa-solid fa-filter me-2 text-primary"></i>{{ __('Select Payroll Period') }}</h5>
      </div>
      <div class="card-body p-4">
        <form action="{{ route('generate.payroll') }}" method="POST">
          @csrf
          <div class="row g-3 align-items-end">
            <div class="col-md-4">
              <label for="year" class="form-label small fw-bold text-dark text-uppercase">{{ __('Year') }}</label>
              <select name="year" class="form-select" id="year">
                @for($y = date('Y'); $y >= date('Y') - 4; $y--)
                  <option value="{{ $y }}" {{ (isset($selectedYear) && $selectedYear == $y) || (!isset($selectedYear) && date('Y') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
              </select>
            </div>
            <div class="col-md-4">
              <label for="month" class="form-label small fw-bold text-dark text-uppercase">{{ __('Month') }}</label>
              <select name="month" class="form-select" id="month">
                @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $mNum => $mName)
                  <option value="{{ $mNum }}" {{ (isset($selectedMonth) && $selectedMonth == $mNum) || (!isset($selectedMonth) && date('n') == $mNum) ? 'selected' : '' }}>{{ $mName }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="fa-solid fa-magnifying-glass me-1"></i> {{ __('Load Payroll Sheet') }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
      <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title fw-bold text-dark mb-0">
          <i class="fa-solid fa-table me-2 text-primary"></i>Payroll Sheet: {{ date('F', mktime(0, 0, 0, $selectedMonth ?? date('m'), 10)) }} {{ $selectedYear ?? date('Y') }}
        </h5>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
          <i class="fa-solid fa-print me-1"></i> Print Sheet
        </button>
      </div>
      <div class="card-body p-3">
        @isset($salaryData)
          <div class="table-responsive">
            <table class="table data-table table-hover align-middle mb-0" style="font-size: .86rem;">
              <thead class="table-light text-uppercase small text-muted">
                <tr>
                  <th scope="col" style="width: 50px;">#</th>
                  <th scope="col">{{ __('Employee') }}</th>
                  <th scope="col">{{ __('Net Salary & Status') }}</th>
                  <th scope="col" class="text-end" style="width: 140px;">{{ __('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($employees as $emp)
                  @php
                    $pay = $salaryData->get($emp->id);
                    $allowancesTotal = $pay ? ($pay->house_rent + $pay->medical + $pay->transport + $pay->special + $pay->bonus + $pay->overtime_pay) : 0;
                  @endphp
                  <tr>
                    <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar-mono flex-shrink-0" style="width:40px;height:40px;background:#09090b;color:#ffffff;font-size:.85rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                          {{ strtoupper(substr($emp->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($emp->lastname ?? 'M', 0, 1)) }}
                        </div>
                        <div>
                          <a href="{{ route('admin.employee.show', $emp->id) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                            {{ $emp->firstname }} {{ $emp->lastname }}
                          </a>
                          <div class="text-muted small">
                            <span class="badge bg-light text-dark border me-1">{{ $emp->unique_id }}</span>
                            <span>{{ optional($emp->department)->title ?? 'General' }}</span>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="fw-bold fs-5 text-success">€ {{ number_format($pay ? $pay->net_salary : 0, 2) }}</span>
                        @if($pay)
                          @if($pay->payment_status === 'paid')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Paid</span>
                          @elseif($pay->payment_status === 'partially_paid')
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning text-dark">Partial (€ {{ number_format($pay->paid_amount, 2) }})</span>
                          @else
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Unpaid</span>
                          @endif
                          @if($pay->is_locked)
                            <span class="badge bg-dark text-white"><i class="fa-solid fa-lock me-1"></i>Locked</span>
                          @endif
                        @else
                          <span class="badge bg-secondary">Not Computed</span>
                        @endif
                      </div>
                      @if($pay)
                        <div class="small text-muted">
                          Gross: €{{ number_format($pay->gross_salary, 2) }} &bull; Deductions: −€{{ number_format($pay->deduction, 2) }}
                        </div>
                      @endif
                    </td>
                    <td class="text-end">
                      @if($pay)
                        <div class="d-inline-flex gap-1">
                          <form action="{{ route('payroll.toggle-lock', $pay->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $pay->is_locked ? 'btn-outline-dark' : 'btn-outline-secondary' }} py-1 px-2" title="{{ $pay->is_locked ? 'Unlock Payroll Record' : 'Lock Payroll Record' }}">
                              <i class="fa-solid {{ $pay->is_locked ? 'fa-lock' : 'fa-lock-open' }}"></i>
                            </button>
                          </form>
                          <a href="{{ route('admin.employee.show', $emp->id) }}#pane-payslip" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Payslip">
                            <i class="fa-solid fa-eye"></i>
                          </a>
                        </div>
                      @else
                        <a href="{{ route('payroll.create', ['year' => $selectedYear, 'month' => $selectedMonth]) }}" class="btn btn-sm btn-outline-success py-1 px-2" title="Compute">
                          <i class="fa-solid fa-calculator me-1"></i>Compute
                        </a>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-file-invoice-dollar fa-3x mb-3 opacity-25"></i>
            <p>Please select a period above to view or export the payroll sheet.</p>
          </div>
        @endisset
      </div>
    </div>
  </div>
</section>
@endsection

@section('script')
@endsection