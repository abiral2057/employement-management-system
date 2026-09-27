@extends('layouts.admin')

@section('title')
    {{ __('Employee Search') }}
@endsection

@section('header')
  <div class="page-header d-flex align-items-center justify-content-between mb-4 pb-3 flex-wrap gap-3">
    <div>
      <h1 class="h1 mb-2">
        <i class="fa-solid fa-magnifying-glass-chart text-dark me-2"></i>{{ __('Employee Search') }}
      </h1>
      <p class="text-muted fs-6 mb-0">Search by Employee ID or Full Name to view complete profile, history &amp; payslip</p>
    </div>
    <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : route('hr.employee.index') ) }}" class="btn btn-outline-secondary">
      <i class="fas fa-list me-1"></i> All Employees
    </a>
  </div>
@endsection

@section('content')

{{-- ── SEARCH BAR ── --}}
<section class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0 emp-search-card">
      <div class="card-body py-4">
        <form method="GET" action="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search') ) }}" id="searchForm" autocomplete="off">
          <div class="emp-search-wrapper">
            <span class="emp-search-icon"><i class="fa-solid fa-search"></i></span>
            <input
              type="text"
              name="query"
              id="empSearchInput"
              class="form-control emp-search-input"
              placeholder="Type Employee ID (e.g. EMP-001) or Employee Name..."
              value="{{ $query }}"
              autocomplete="off"
            />
            <button type="submit" class="btn btn-primary emp-search-btn" id="searchBtn">
              <i class="fas fa-search me-1"></i> Search
            </button>
            @if($query)
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search') ) }}" class="btn btn-outline-secondary emp-clear-btn">
              <i class="fas fa-times"></i>
            </a>
            @endif
          </div>
          {{-- Autocomplete Dropdown --}}
          <div id="autocompleteDropdown" class="emp-autocomplete-dropdown d-none"></div>
        </form>
        <p class="text-muted small mt-2 mb-0"><i class="fas fa-lightbulb me-1 text-warning"></i> Tip: Use exact Employee ID for instant lookup. Start typing a name to see live suggestions.</p>
      </div>
    </div>
  </div>
</section>

{{-- ── MULTIPLE RESULTS LIST ── --}}
@if($results->count() > 0)
<section class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-warning bg-opacity-10 border-0">
        <h5 class="card-title mb-0 text-warning-dark">
          <i class="fas fa-users me-2"></i>{{ $results->count() }} employees found matching "<strong>{{ $query }}</strong>" — click a name to view full profile
        </h5>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light text-uppercase small text-muted">
            <tr>
              <th style="width: 50px;">#</th>
              <th>{{ __('Employee Name') }}</th>
              <th class="text-end" style="width: 140px;">{{ __('Action') }}</th>
            </tr>
          </thead>
          <tbody>
            @foreach($results as $i => $emp)
            <tr>
              <td class="text-muted fw-bold">{{ $i + 1 }}</td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div class="avatar-mono flex-shrink-0" style="width:42px;height:42px;background:#09090b;color:#ffffff;font-size:.9rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    {{ strtoupper(substr($emp->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($emp->lastname ?? 'M', 0, 1)) }}
                  </div>
                  <div>
                    <a href="{{ (Auth::user()->role->slug === 'super-admin' ? route('employee.show', $emp->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.show', $emp->id) : route('hr.employee.show', $emp->id) )) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                      {{ $emp->firstname }} {{ $emp->lastname }}
                    </a>
                    <div class="small text-muted">
                      <span class="badge bg-light text-dark border me-1">{{ $emp->unique_id }}</span>
                      <span>{{ optional($emp->department)->title ?? 'General' }}</span> &bull; <span>{{ optional($emp->designation)->title ?? 'Staff' }}</span>
                      @if($emp->status == 1)
                        <span class="badge bg-success bg-opacity-10 text-success ms-1">Active</span>
                      @else
                        <span class="badge bg-secondary ms-1">Inactive</span>
                      @endif
                    </div>
                  </div>
                </div>
              </td>
              <td class="text-end">
                <a href="{{ (Auth::user()->role->slug === 'super-admin' ? route('employee.show', $emp->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.show', $emp->id) : route('hr.employee.show', $emp->id) )) }}" class="btn btn-dark btn-sm fw-semibold px-3">
                  {{ __('Profile') }} &rarr;
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endif

{{-- ── EMPTY SEARCH STATE ── --}}
@if(!$query && $results->count() === 0 && !$employee)
<section class="row justify-content-center">
  <div class="col-md-6 text-center py-5">
    <div class="emp-empty-state">
      <div class="emp-empty-icon mb-4"><i class="fa-solid fa-user-magnifying-glass"></i></div>
      <h4 class="text-muted">Search for an Employee</h4>
      <p class="text-muted small">Enter an Employee ID or name in the search bar above to retrieve their complete profile, employment history, salary statement, and payslip.</p>
    </div>
  </div>
</section>
@endif

{{-- ── NOT FOUND STATE ── --}}
@if($query && !$employee && $results->count() === 0)
<section class="row justify-content-center">
  <div class="col-md-6 text-center py-5">
    <div class="emp-empty-state">
      <div class="emp-empty-icon text-danger mb-4"><i class="fa-solid fa-circle-xmark"></i></div>
      <h4 class="text-danger">No Employee Found</h4>
      <p class="text-muted small">No employee matches "<strong>{{ $query }}</strong>". Try searching by Employee ID (e.g. <code>EMP-001</code>) or a different name.</p>
    </div>
  </div>
</section>
@endif

{{-- ── EMPLOYEE PROFILE ── --}}
@if($employee)
<section class="row mb-4">
  {{-- Profile Hero Card --}}
  <div class="col-12">
    <div class="card border-0 shadow-sm emp-profile-hero mb-4">
      <div class="emp-hero-banner"></div>
      <div class="card-body pt-0">
        <div class="d-flex flex-wrap align-items-end gap-3 emp-hero-body">
          {{-- Avatar --}}
          <div class="emp-avatar-wrap">
            <div class="emp-avatar">
              {{ strtoupper(substr($employee->firstname, 0, 1)) }}{{ strtoupper(substr($employee->lastname, 0, 1)) }}
            </div>
          </div>
          {{-- Name & Info --}}
          <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
              <h2 class="emp-hero-name mb-0">{{ $employee->firstname }} {{ $employee->lastname }}</h2>
              @if($employee->status == 1)
                <span class="badge bg-success emp-status-badge"><i class="fas fa-circle me-1" style="font-size:.5rem;vertical-align:middle;"></i>Active</span>
              @else
                <span class="badge bg-secondary emp-status-badge">Inactive</span>
              @endif
            </div>
            <div class="d-flex flex-wrap gap-3 emp-hero-meta text-muted">
              <span><i class="fa-solid fa-id-badge me-1 text-primary"></i><strong>{{ $employee->unique_id }}</strong></span>
              <span><i class="fa-solid fa-building me-1 text-info"></i>{{ optional($employee->department)->title ?? '—' }}</span>
              <span><i class="fa-solid fa-briefcase me-1 text-warning"></i>{{ optional($employee->designation)->title ?? '—' }}</span>
              @if($employee->email)
              <span><i class="fas fa-envelope me-1 text-success"></i>{{ $employee->email }}</span>
              @endif
            </div>
          </div>
          {{-- Quick Actions --}}
          <div class="d-flex gap-2 flex-wrap emp-hero-actions">
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id) ) }}" class="btn btn-outline-primary btn-sm">
              <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-print me-1"></i> Print Profile
            </button>
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : route('hr.employee.index') ) }}" class="btn btn-outline-secondary btn-sm">
              <i class="fas fa-list me-1"></i> All Employees
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Tab Navigation --}}
  <div class="col-12">
    <ul class="nav emp-tab-nav mb-3" id="profileTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn active" id="tab-profile" data-bs-toggle="tab" data-bs-target="#pane-profile" type="button" role="tab">
          <i class="fas fa-id-card me-2"></i>Profile
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" id="tab-attendance" data-bs-toggle="tab" data-bs-target="#pane-attendance" type="button" role="tab">
          <i class="fas fa-calendar-check me-2"></i>Attendance
          <span class="emp-tab-count">{{ $employee->attendances->count() }}</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" id="tab-salary" data-bs-toggle="tab" data-bs-target="#pane-salary" type="button" role="tab">
          <i class="fas fa-money-bill-wave me-2"></i>Salary Statement
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" id="tab-payslip" data-bs-toggle="tab" data-bs-target="#pane-payslip" type="button" role="tab">
          <i class="fas fa-file-invoice-dollar me-2"></i>Payslip
          <span class="emp-tab-count">{{ $employee->payrolls->count() }}</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" id="tab-leaves" data-bs-toggle="tab" data-bs-target="#pane-leaves" type="button" role="tab">
          <i class="fas fa-person-walking-arrow-right me-2"></i>Leave History
          <span class="emp-tab-count">{{ $employee->leaves->count() }}</span>
        </button>
      </li>
    </ul>

    <div class="tab-content" id="profileTabsContent">

      {{-- ──────────────── TAB 1: PROFILE ──────────────── --}}
      <div class="tab-pane fade show active" id="pane-profile" role="tabpanel">
        <div class="row g-3">
          {{-- Personal Information --}}
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-user me-2 text-primary"></i>Personal Information
              </div>
              <div class="card-body">
                <div class="emp-info-grid">
                  <div class="emp-info-item">
                    <span class="emp-info-label">Employee ID</span>
                    <span class="emp-info-value fw-semibold text-primary">{{ $employee->unique_id }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Full Name</span>
                    <span class="emp-info-value">{{ $employee->firstname }} {{ $employee->lastname }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Email</span>
                    <span class="emp-info-value">{{ $employee->email ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Phone</span>
                    <span class="emp-info-value">{{ $employee->phone ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Date of Birth</span>
                    <span class="emp-info-value">{{ $employee->dob ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Gender</span>
                    <span class="emp-info-value">
                      @if($employee->gender == 0) Male
                      @elseif($employee->gender == 1) Female
                      @else {{ $employee->gender ?? '—' }}
                      @endif
                    </span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Religion</span>
                    <span class="emp-info-value">{{ $employee->religion ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Marital Status</span>
                    <span class="emp-info-value">{{ ucfirst($employee->marital ?? '—') }}</span>
                  </div>
                  <div class="emp-info-item emp-info-full">
                    <span class="emp-info-label">Address</span>
                    <span class="emp-info-value">{{ $employee->address ?? '—' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Employment Details --}}
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-briefcase me-2 text-warning"></i>Employment Details
              </div>
              <div class="card-body">
                <div class="emp-info-grid">
                  <div class="emp-info-item">
                    <span class="emp-info-label">Department</span>
                    <span class="emp-info-value">{{ optional($employee->department)->title ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Designation</span>
                    <span class="emp-info-value">{{ optional($employee->designation)->title ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Schedule</span>
                    <span class="emp-info-value">{{ optional($employee->schedule)->title ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Status</span>
                    <span class="emp-info-value">
                      @if($employee->status == 1)
                        <span class="badge bg-success">Active</span>
                      @else
                        <span class="badge bg-secondary">Inactive</span>
                      @endif
                    </span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Date Joined</span>
                    <span class="emp-info-value">{{ $employee->created_at->format('d M Y') }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Tenure</span>
                    <span class="emp-info-value">{{ $employee->created_at->diffForHumans() }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Total Leaves</span>
                    <span class="emp-info-value">{{ $employee->leaves->count() }} records</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Payroll Records</span>
                    <span class="emp-info-value">{{ $employee->payrolls->count() }} months</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ──────────────── TAB 2: ATTENDANCE ──────────────── --}}
      <div class="tab-pane fade" id="pane-attendance" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-calendar-check me-2 text-info"></i>Attendance History</span>
            <span class="badge bg-info text-dark">{{ $employee->attendances->count() }} records</span>
          </div>
          @if($employee->attendances->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="attendanceTable">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Working Hours</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->attendances->sortByDesc('date') as $i => $att)
                  <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>{{ isset($att->date) ? \Carbon\Carbon::parse($att->date)->format('d M Y') : '—' }}</td>
                    <td>{{ $att->check_in ?? '—' }}</td>
                    <td>{{ $att->check_out ?? '—' }}</td>
                    <td>
                      @if(isset($att->status))
                        @if($att->status == 'present' || $att->status == 1)
                          <span class="badge bg-success">Present</span>
                        @elseif($att->status == 'absent' || $att->status == 0)
                          <span class="badge bg-danger">Absent</span>
                        @elseif($att->status == 'late')
                          <span class="badge bg-warning text-dark">Late</span>
                        @else
                          <span class="badge bg-secondary">{{ $att->status }}</span>
                        @endif
                      @else
                        <span class="badge bg-success">Present</span>
                      @endif
                    </td>
                    <td>{{ $att->working_hours ?? '—' }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @else
          <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-calendar-xmark fa-3x mb-3 opacity-25"></i>
            <p>No attendance records found for this employee.</p>
          </div>
          @endif
        </div>
      </div>

      {{-- ──────────────── TAB 3: SALARY STATEMENT ──────────────── --}}
      <div class="tab-pane fade" id="pane-salary" role="tabpanel">
        @if($employee->salary)
        @php $sal = $employee->salary; @endphp
        <div class="row g-3">
          {{-- Earnings --}}
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-circle-plus me-2 text-success"></i>Earnings / Allowances
              </div>
              <div class="card-body">
                <div class="emp-salary-row">
                  <span>Basic Salary</span>
                  <span class="fw-semibold">€ {{ number_format($sal->basic ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>House Rent</span>
                  <span>€ {{ number_format($sal->house_rent ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Medical Allowance</span>
                  <span>€ {{ number_format($sal->medical ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Transport Allowance</span>
                  <span>€ {{ number_format($sal->transport ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Phone Bill</span>
                  <span>€ {{ number_format($sal->phone_bill ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Internet Bill</span>
                  <span>€ {{ number_format($sal->internet_bill ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Special Allowance</span>
                  <span>€ {{ number_format($sal->special ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Overtime Pay</span>
                  <span>€ {{ number_format($sal->overtime_pay ?? 0, 2) }}</span>
                </div>
                @php
                  $grossEarnings = ($sal->basic ?? 0) + ($sal->house_rent ?? 0) + ($sal->medical ?? 0) +
                    ($sal->transport ?? 0) + ($sal->phone_bill ?? 0) + ($sal->internet_bill ?? 0) +
                    ($sal->special ?? 0) + ($sal->overtime_pay ?? 0);
                @endphp
                <div class="emp-salary-total bg-success bg-opacity-10">
                  <span class="fw-bold text-success">Total Earnings</span>
                  <span class="fw-bold text-success fs-5">€ {{ number_format($grossEarnings, 2) }}</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Deductions --}}
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-circle-minus me-2 text-danger"></i>Deductions
              </div>
              <div class="card-body">
                <div class="emp-salary-row">
                  <span>Provident Fund</span>
                  <span>€ {{ number_format($sal->provident_fund ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Income Tax</span>
                  <span>€ {{ number_format($sal->income_tax ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Health Insurance</span>
                  <span>€ {{ number_format($sal->health_insurance ?? 0, 2) }}</span>
                </div>
                <div class="emp-salary-row">
                  <span>Life Insurance</span>
                  <span>€ {{ number_format($sal->life_insurance ?? 0, 2) }}</span>
                </div>
                @php
                  $totalDeductions = ($sal->provident_fund ?? 0) + ($sal->income_tax ?? 0) +
                    ($sal->health_insurance ?? 0) + ($sal->life_insurance ?? 0);
                  $netSalary = $grossEarnings - $totalDeductions;
                @endphp
                <div class="emp-salary-total bg-danger bg-opacity-10">
                  <span class="fw-bold text-danger">Total Deductions</span>
                  <span class="fw-bold text-danger fs-5">€ {{ number_format($totalDeductions, 2) }}</span>
                </div>

                <div class="emp-net-salary-box mt-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6">Net Take-Home Salary</span>
                    <span class="fw-bold fs-4 text-primary">€ {{ number_format($netSalary, 2) }}</span>
                  </div>
                  <div class="text-muted small mt-1">Total Earnings − Total Deductions = Net Salary</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        @else
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-wallet fa-3x mb-3 opacity-25"></i>
            <p>No salary structure defined for this employee yet.</p>
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id) ) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-plus me-1"></i> Add Salary Structure
            </a>
          </div>
        </div>
        @endif
      </div>

      {{-- ──────────────── TAB 4: PAYSLIP ──────────────── --}}
      <div class="tab-pane fade" id="pane-payslip" role="tabpanel">
        @if($employee->payrolls->count() > 0)
        @php
          $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
          $payrolls = $employee->payrolls->sortByDesc(fn($p) => ($p->year * 100) + $p->month);
        @endphp

        <div class="row g-3 mb-3">
          <div class="col-12">
            <p class="text-muted small"><i class="fas fa-info-circle me-1 text-info"></i>{{ $employee->payrolls->count() }} payroll record(s) found. Click "Print Payslip" on any month to generate a printable payslip.</p>
          </div>
        </div>

        <div class="row g-3" id="payslipCards">
          @foreach($payrolls as $payroll)
          <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm emp-payslip-card">
              <div class="card-header emp-payslip-header">
                <div>
                  <div class="fw-bold">{{ $months[$payroll->month] ?? 'Month '.$payroll->month }} {{ $payroll->year }}</div>
                  <div class="text-muted small">Payroll Period</div>
                </div>
                <span class="emp-payslip-badge">
                  <i class="fas fa-file-invoice-dollar me-1"></i>Payslip
                </span>
              </div>
              <div class="card-body">
                <div class="emp-payslip-row"><span>Basic</span><span>€ {{ number_format($payroll->basic ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>House Rent</span><span>€ {{ number_format($payroll->house_rent ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Medical</span><span>€ {{ number_format($payroll->medical ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Gross Salary</span><span class="fw-semibold text-success">€ {{ number_format($payroll->gross_salary ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Deductions</span><span class="text-danger">− € {{ number_format($payroll->deduction ?? 0, 2) }}</span></div>
                @if($payroll->days_present || $payroll->days_absent)
                <div class="emp-payslip-row"><span>Days Present</span><span class="badge bg-success">{{ $payroll->days_present ?? 0 }} days</span></div>
                <div class="emp-payslip-row"><span>Days Absent</span><span class="badge bg-danger">{{ $payroll->days_absent ?? 0 }} days</span></div>
                @endif
                <div class="emp-payslip-net">
                  <span>Net Salary</span>
                  <span class="emp-payslip-net-amount">€ {{ number_format($payroll->net_salary ?? 0, 2) }}</span>
                </div>
              </div>
              <div class="card-footer bg-transparent border-0 pt-0">
                <button onclick="printPayslip({{ $payroll->id }}, '{{ $employee->firstname }} {{ $employee->lastname }}', '{{ $employee->unique_id }}', '{{ optional($employee->department)->title }}', '{{ optional($employee->designation)->title }}', '{{ $months[$payroll->month] ?? $payroll->month }} {{ $payroll->year }}', {{ $payroll->basic ?? 0 }}, {{ $payroll->house_rent ?? 0 }}, {{ $payroll->medical ?? 0 }}, {{ $payroll->gross_salary ?? 0 }}, {{ $payroll->deduction ?? 0 }}, {{ $payroll->net_salary ?? 0 }}, {{ $payroll->days_present ?? 0 }}, {{ $payroll->days_absent ?? 0 }})"
                  class="btn btn-primary btn-sm w-100">
                  <i class="fas fa-print me-1"></i> Print Payslip
                </button>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="card border-0 shadow-sm">
          <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-file-invoice-dollar fa-3x mb-3 opacity-25"></i>
            <p>No payroll records found for this employee.</p>
          </div>
        </div>
        @endif
      </div>

      {{-- ──────────────── TAB 5: LEAVE HISTORY ──────────────── --}}
      <div class="tab-pane fade" id="pane-leaves" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-person-walking-arrow-right me-2 text-warning"></i>Leave History</span>
            <span class="badge bg-warning text-dark">{{ $employee->leaves->count() }} records</span>
          </div>
          @if($employee->leaves->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0" id="leaveTable">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Leave Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->leaves->sortByDesc('from_date') as $i => $leave)
                  <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>{{ $leave->leave_type ?? $leave->type ?? '—' }}</td>
                    <td>{{ isset($leave->from_date) ? \Carbon\Carbon::parse($leave->from_date)->format('d M Y') : '—' }}</td>
                    <td>{{ isset($leave->to_date) ? \Carbon\Carbon::parse($leave->to_date)->format('d M Y') : '—' }}</td>
                    <td>
                      @if(isset($leave->from_date) && isset($leave->to_date))
                        {{ \Carbon\Carbon::parse($leave->from_date)->diffInDays(\Carbon\Carbon::parse($leave->to_date)) + 1 }} days
                      @else
                        —
                      @endif
                    </td>
                    <td>{{ Str::limit($leave->reason ?? '—', 40) }}</td>
                    <td>
                      @php $status = $leave->status ?? 'pending'; @endphp
                      @if($status == 'approved' || $status == 1)
                        <span class="badge bg-success">Approved</span>
                      @elseif($status == 'rejected' || $status == 2)
                        <span class="badge bg-danger">Rejected</span>
                      @else
                        <span class="badge bg-warning text-dark">Pending</span>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @else
          <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
            <p>No leave records found for this employee.</p>
          </div>
          @endif
        </div>
      </div>

    </div>{{-- /tab-content --}}
  </div>
</section>
@endif

@endsection

@section('script')
<style>
/* ── Search card ── */
.emp-search-card { border-radius: 16px; }
.emp-search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 10px;
}
.emp-search-icon {
  position: absolute;
  left: 16px;
  color: #6c757d;
  font-size: 1rem;
  z-index: 2;
  pointer-events: none;
}
.emp-search-input {
  padding-left: 44px !important;
  height: 52px;
  font-size: 1rem;
  border-radius: 12px !important;
  border: 2px solid #e0e6ed;
  transition: border-color .2s, box-shadow .2s;
  flex: 1;
}
.emp-search-input:focus {
  border-color: #3b7ddd;
  box-shadow: 0 0 0 3px rgba(59,125,221,.15);
  outline: none;
}
.emp-search-btn {
  height: 52px;
  border-radius: 12px !important;
  padding: 0 24px;
  font-weight: 600;
  white-space: nowrap;
}
.emp-clear-btn {
  height: 52px;
  border-radius: 12px !important;
  padding: 0 16px;
}

/* ── Autocomplete dropdown ── */
.emp-autocomplete-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e0e6ed;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,.12);
  z-index: 999;
  max-height: 320px;
  overflow-y: auto;
}
.emp-autocomplete-item {
  padding: 10px 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: background .15s;
}
.emp-autocomplete-item:hover { background: #f4f4f5; }
.emp-ac-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: #09090b;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .75rem; font-weight: 700; flex-shrink: 0;
}
.emp-ac-info { flex: 1; min-width: 0; }
.emp-ac-name { font-weight: 600; font-size: .9rem; color: #09090b; }
.emp-ac-meta { font-size: .78rem; color: #71717a; }
.emp-ac-id { font-size: .78rem; color: #09090b; font-weight: 700; background: #f4f4f5; padding: 2px 6px; border-radius: 4px; }

/* ── Empty state ── */
.emp-empty-state { }
.emp-empty-icon { font-size: 4rem; color: #d4d4d8; }

/* ── Profile hero ── */
.emp-profile-hero { border-radius: 14px; overflow: hidden; border: 1px solid #e4e4e7; background: #fff; }
.emp-hero-banner {
  height: 80px;
  background: #09090b;
}
.emp-hero-body { padding: 0 16px 16px; margin-top: -28px; }
.emp-avatar-wrap { flex-shrink: 0; }
.emp-avatar {
  width: 72px; height: 72px; border-radius: 50%;
  background: #09090b;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.4rem; font-weight: 700;
  border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.emp-hero-name { font-size: 1.4rem; font-weight: 750; color: #09090b; }
.emp-status-badge { font-size: .7rem; }
.emp-hero-meta { font-size: .875rem; color: #52525b; }
.emp-hero-actions { margin-top: 8px; }

/* ── Tabs ── */
.emp-tab-nav {
  display: flex;
  gap: 4px;
  border-bottom: 2px solid #e0e6ed;
  flex-wrap: wrap;
}
.emp-tab-btn {
  background: none;
  border: none;
  padding: 10px 18px;
  font-size: .9rem;
  font-weight: 500;
  color: #6c757d;
  border-radius: 8px 8px 0 0;
  cursor: pointer;
  position: relative;
  transition: color .2s, background .2s;
  display: flex; align-items: center; gap: 4px;
}
.emp-tab-btn:hover { color: #3b7ddd; background: #f0f4ff; }
.emp-tab-btn.active {
  color: #3b7ddd;
  background: #fff;
  border-bottom: 2px solid #3b7ddd;
  margin-bottom: -2px;
}
.emp-tab-count {
  background: #3b7ddd; color: #fff;
  font-size: .68rem; font-weight: 700;
  border-radius: 10px; padding: 1px 6px;
  line-height: 1.4;
}

/* ── Section headers ── */
.emp-section-header {
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  font-weight: 600;
  font-size: .9rem;
  padding: 12px 16px;
}

/* ── Info grid ── */
.emp-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}
.emp-info-item {
  padding: 10px 4px;
  border-bottom: 1px solid #f1f3f5;
  display: flex; flex-direction: column; gap: 2px;
}
.emp-info-item:last-child, .emp-info-item:nth-last-child(2):not(.emp-info-full) { border-bottom: none; }
.emp-info-full { grid-column: 1 / -1; }
.emp-info-label { font-size: .75rem; color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.emp-info-value { font-size: .9rem; color: #212529; }

/* ── Salary rows ── */
.emp-salary-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 8px 0; border-bottom: 1px solid #f1f3f5; font-size: .9rem;
}
.emp-salary-row:last-child { border-bottom: none; }
.emp-salary-total {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px; border-radius: 10px; margin-top: 8px;
}
.emp-net-salary-box {
  background: #f4f4f5;
  border: 1px solid #e4e4e7;
  border-radius: 10px; padding: 16px;
}

/* ── Payslip cards ── */
.emp-payslip-card { border-radius: 12px; transition: transform .2s, box-shadow .2s; border: 1px solid #e4e4e7; }
.emp-payslip-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06) !important; }
.emp-payslip-header {
  display: flex; justify-content: space-between; align-items: center;
  background: #09090b;
  color: #fff; border-radius: 12px 12px 0 0 !important; padding: 14px 16px;
}
.emp-payslip-badge {
  background: #27272a;
  border-radius: 6px; padding: 4px 10px; font-size: .75rem; color: #f4f4f5;
}
.emp-payslip-row {
  display: flex; justify-content: space-between;
  padding: 6px 0; border-bottom: 1px solid #f4f4f5; font-size: .875rem;
}
.emp-payslip-net {
  display: flex; justify-content: space-between; align-items: center;
  background: #f4f4f5;
  border: 1px solid #e4e4e7;
  border-radius: 8px; padding: 10px 12px; margin-top: 8px;
  font-weight: 600;
}
.emp-payslip-net-amount { color: #09090b; font-size: 1.15rem; font-weight: 800; }

/* ── Print: hide sidebar/navbar ── */
@media print {
  #sidebar, .main > nav, .footer, .emp-hero-actions, .emp-search-card,
  .nav, .emp-tab-nav, .tab-pane:not(.active) { display: none !important; }
  .tab-pane.active { display: block !important; }
  body, .main, .content { margin: 0 !important; padding: 0 !important; }
}
</style>

<script>
// ── Bootstrap tab activation ──
document.querySelectorAll('.emp-tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.emp-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => { p.classList.remove('show', 'active'); });
    this.classList.add('active');
    const target = document.querySelector(this.getAttribute('data-bs-target'));
    if (target) { target.classList.add('show', 'active'); }
  });
});

// ── Live AJAX Autocomplete ──
(function() {
  const input  = document.getElementById('empSearchInput');
  const dropdown = document.getElementById('autocompleteDropdown');
  if (!input || !dropdown) return;

  const ajaxUrl = "{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search.ajax') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search.ajax') : route('hr.employee.search.ajax')) }}";
  const profileUrl = "{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}";

  let debounceTimer;
  function initials(name) {
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
  }

  input.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const q = this.value.trim();
    if (q.length < 1) { dropdown.classList.add('d-none'); dropdown.innerHTML = ''; return; }

    debounceTimer = setTimeout(() => {
      fetch(`${ajaxUrl}?query=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          dropdown.innerHTML = '';
          if (!data.length) {
            dropdown.innerHTML = '<div class="emp-autocomplete-item text-muted"><span>No employees found</span></div>';
          } else {
            data.forEach(emp => {
              const item = document.createElement('div');
              item.className = 'emp-autocomplete-item';
              item.innerHTML = `
                <div class="emp-ac-avatar">${initials(emp.name)}</div>
                <div class="emp-ac-info">
                  <div class="emp-ac-name">${emp.name}</div>
                  <div class="emp-ac-meta">${emp.department} &middot; ${emp.designation}</div>
                </div>
                <span class="emp-ac-id">${emp.unique_id}</span>`;
              item.addEventListener('click', () => {
                window.location.href = `${profileUrl}?query=${encodeURIComponent(emp.unique_id)}`;
              });
              dropdown.appendChild(item);
            });
          }
          dropdown.classList.remove('d-none');
          // position relative to wrapper
          dropdown.style.left = '0';
          dropdown.style.right = '0';
        });
    }, 220);
  });

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add('d-none');
    }
  });

  // Reposition dropdown inside wrapper
  const wrapper = document.querySelector('.emp-search-wrapper');
  if (wrapper) {
    wrapper.style.position = 'relative';
    wrapper.appendChild(dropdown);
    dropdown.style.position = 'absolute';
    dropdown.style.top = '100%';
    dropdown.style.zIndex = '999';
    dropdown.style.width = '100%';
  }
})();

// ── Print Payslip ──
function printPayslip(id, name, empId, dept, desig, period, basic, houseRent, medical, gross, deduction, net, present, absent) {
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Payslip — ${name} — ${period}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 30px; }
    .payslip { max-width: 700px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; box-shadow: 0 4px 16px rgba(0,0,0,.08); }
    .ps-header { background: #09090b; color: #fff; padding: 28px 32px; }
    .ps-header h2 { font-size: 1.5rem; margin-bottom: 4px; letter-spacing: -0.02em; }
    .ps-header p { color: #a1a1aa; font-size: .9rem; }
    .ps-badge { background: #27272a; color: #f4f4f5; padding: 4px 12px; border-radius: 6px; font-size: .8rem; display: inline-block; margin-top: 8px; }
    .ps-body { padding: 28px 32px; }
    .ps-employee-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; background: #f4f4f5; border-radius: 8px; padding: 16px; }
    .ps-emp-item { }
    .ps-emp-label { font-size: .72rem; color: #71717a; text-transform: uppercase; letter-spacing: .05em; }
    .ps-emp-value { font-size: .9rem; font-weight: 700; color: #09090b; margin-top: 2px; }
    .ps-section { margin-bottom: 20px; }
    .ps-section-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #71717a; border-bottom: 1px solid #e4e4e7; padding-bottom: 6px; margin-bottom: 10px; }
    .ps-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: .88rem; border-bottom: 1px dashed #e4e4e7; }
    .ps-total { display: flex; justify-content: space-between; padding: 10px 14px; border-radius: 6px; font-weight: 700; margin-top: 6px; }
    .ps-total.earn { background: #ecfdf5; color: #065f46; }
    .ps-total.ded { background: #fef2f2; color: #991b1b; }
    .ps-net { background: #09090b; color: #fff; padding: 18px 24px; border-radius: 8px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
    .ps-net-label { font-size: 1rem; font-weight: 600; }
    .ps-net-amount { font-size: 2rem; font-weight: 800; }
    .ps-footer { text-align: center; padding: 16px; font-size: .78rem; color: #6c757d; border-top: 1px solid #eee; }
    @media print { body { padding: 10px; background: #fff; } }
  </style>
</head>
<body>
<div class="payslip">
  <div class="ps-header">
    <h2>PAYSLIP</h2>
    <p>Pay Period: ${period}</p>
    <span class="ps-badge">Document No. PS-${id}</span>
  </div>
  <div class="ps-body">
    <div class="ps-employee-grid">
      <div class="ps-emp-item"><div class="ps-emp-label">Employee Name</div><div class="ps-emp-value">${name}</div></div>
      <div class="ps-emp-item"><div class="ps-emp-label">Employee ID</div><div class="ps-emp-value">${empId}</div></div>
      <div class="ps-emp-item"><div class="ps-emp-label">Department</div><div class="ps-emp-value">${dept}</div></div>
      <div class="ps-emp-item"><div class="ps-emp-label">Designation</div><div class="ps-emp-value">${desig}</div></div>
      <div class="ps-emp-item"><div class="ps-emp-label">Days Present</div><div class="ps-emp-value">${present} days</div></div>
      <div class="ps-emp-item"><div class="ps-emp-label">Days Absent</div><div class="ps-emp-value">${absent} days</div></div>
    </div>

    <div class="ps-section">
      <div class="ps-section-title">Earnings</div>
      <div class="ps-row"><span>Basic Salary</span><span>€ ${parseFloat(basic).toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>
      <div class="ps-row"><span>House Rent Allowance</span><span>€ ${parseFloat(houseRent).toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>
      <div class="ps-row"><span>Medical Allowance</span><span>€ ${parseFloat(medical).toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>
      <div class="ps-total earn"><span>Gross Salary</span><span>€ ${parseFloat(gross).toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>
    </div>

    <div class="ps-section">
      <div class="ps-section-title">Deductions</div>
      <div class="ps-row"><span>Total Deductions</span><span>− € ${parseFloat(deduction).toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>
      <div class="ps-total ded"><span>Total Deducted</span><span>− € ${parseFloat(deduction).toLocaleString('en-US', {minimumFractionDigits:2})}</span></div>
    </div>

    <div class="ps-net">
      <div class="ps-net-label">Net Take-Home Salary</div>
      <div class="ps-net-amount">€ ${parseFloat(net).toLocaleString('en-US', {minimumFractionDigits:2})}</div>
    </div>
  </div>
  <div class="ps-footer">
    This is a system-generated payslip and does not require a signature. &bull; Generated on ${new Date().toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'})}
  </div>
</div>
<script>
  window.onload = function() { window.print(); };
<\/script>
</body></html>`;
  const win = window.open('', '_blank', 'width=800,height=700');
  win.document.write(html);
  win.document.close();
}
</script>
@endsection
