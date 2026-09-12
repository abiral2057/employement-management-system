@extends('layouts.admin')

@section('title')
  {{ $employee->firstname }} {{ $employee->lastname }} — Employee Profile
@endsection

@section('header')
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1" style="font-size:.82rem;">
          <li class="breadcrumb-item">
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('super.dashboard') : (Auth::user()->role->slug === 'administrator' ? route('admin.dashboard') : route('hr.dashboard')) }}">Dashboard</a>
          </li>
          <li class="breadcrumb-item">
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : route('hr.employee.index')) }}">Employees</a>
          </li>
          <li class="breadcrumb-item active">{{ $employee->firstname }} {{ $employee->lastname }}</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0">
        <i class="fas fa-id-card text-primary me-2"></i>Employee Profile
      </h1>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-search me-1"></i> Search
      </a>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id)) }}" class="btn btn-outline-warning btn-sm">
        <i class="fas fa-edit me-1"></i> Edit
      </a>
      <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-print me-1"></i> Print
      </button>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : route('hr.employee.index')) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')

{{-- ── Profile Hero Card ── --}}
<section class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm emp-profile-hero">
      <div class="emp-hero-banner"></div>
      <div class="card-body pt-0">
        <div class="d-flex flex-wrap align-items-end gap-3 emp-hero-body">
          {{-- Avatar --}}
          <div class="emp-avatar-wrap">
            <div class="emp-avatar">
              {{ strtoupper(substr($employee->firstname, 0, 1)) }}{{ strtoupper(substr($employee->lastname, 0, 1)) }}
            </div>
          </div>
          {{-- Name & meta --}}
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
              @if($employee->phone)
              <span><i class="fas fa-phone me-1 text-secondary"></i>{{ $employee->phone }}</span>
              @endif
            </div>
          </div>
          {{-- Stat Pills --}}
          <div class="d-flex gap-2 flex-wrap emp-stat-pills">
            <div class="emp-stat-pill">
              <span class="emp-stat-num">{{ $employee->attendances->count() }}</span>
              <span class="emp-stat-label">Attendance</span>
            </div>
            <div class="emp-stat-pill">
              <span class="emp-stat-num">{{ $employee->payrolls->count() }}</span>
              <span class="emp-stat-label">Payrolls</span>
            </div>
            <div class="emp-stat-pill">
              <span class="emp-stat-num">{{ $employee->leaves->count() }}</span>
              <span class="emp-stat-label">Leaves</span>
            </div>
            <div class="emp-stat-pill">
              <span class="emp-stat-num">{{ $employee->created_at->diffInMonths(now()) }}</span>
              <span class="emp-stat-label">Months</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── Tab Navigation ── --}}
<section class="row mb-4">
  <div class="col-12">
    <ul class="nav emp-tab-nav mb-3" id="profileTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn active" data-bs-toggle="tab" data-bs-target="#pane-profile" type="button" role="tab">
          <i class="fas fa-id-card me-2"></i>Profile
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-attendance" type="button" role="tab">
          <i class="fas fa-calendar-check me-2"></i>Attendance
          <span class="emp-tab-count">{{ $employee->attendances->count() }}</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-salary" type="button" role="tab">
          <i class="fas fa-money-bill-wave me-2"></i>Salary Statement
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-payslip" type="button" role="tab">
          <i class="fas fa-file-invoice-dollar me-2"></i>Payslip
          <span class="emp-tab-count">{{ $employee->payrolls->count() }}</span>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-leaves" type="button" role="tab">
          <i class="fas fa-person-walking-arrow-right me-2"></i>Leave History
          <span class="emp-tab-count">{{ $employee->leaves->count() }}</span>
        </button>
      </li>
    </ul>

    <div class="tab-content" id="profileTabsContent">

      {{-- ──────── TAB 1: PROFILE ──────── --}}
      <div class="tab-pane fade show active" id="pane-profile" role="tabpanel">
        <div class="row g-3">
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
                  <div class="emp-info-item">
                    <span class="emp-info-label">Attendance Records</span>
                    <span class="emp-info-value">{{ $employee->attendances->count() }} records</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Late Records</span>
                    <span class="emp-info-value">{{ $employee->lateTime->count() }} records</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ──────── TAB 2: ATTENDANCE ──────── --}}
      <div class="tab-pane fade" id="pane-attendance" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-calendar-check me-2 text-info"></i>Attendance History</span>
            <span class="badge bg-info text-dark">{{ $employee->attendances->count() }} records</span>
          </div>
          @if($employee->attendances->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0 data-table-att">
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

      {{-- ──────── TAB 3: SALARY STATEMENT ──────── --}}
      <div class="tab-pane fade" id="pane-salary" role="tabpanel">
        @if($employee->salary)
        @php $sal = $employee->salary; @endphp
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-circle-plus me-2 text-success"></i>Earnings / Allowances
              </div>
              <div class="card-body">
                <div class="emp-salary-row"><span>Basic Salary</span><span class="fw-semibold">৳ {{ number_format($sal->basic ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>House Rent</span><span>৳ {{ number_format($sal->house_rent ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Medical Allowance</span><span>৳ {{ number_format($sal->medical ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Transport Allowance</span><span>৳ {{ number_format($sal->transport ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Phone Bill</span><span>৳ {{ number_format($sal->phone_bill ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Internet Bill</span><span>৳ {{ number_format($sal->internet_bill ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Special Allowance</span><span>৳ {{ number_format($sal->special ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Overtime Pay</span><span>৳ {{ number_format($sal->overtime_pay ?? 0, 2) }}</span></div>
                @php
                  $grossEarnings = ($sal->basic ?? 0) + ($sal->house_rent ?? 0) + ($sal->medical ?? 0) +
                    ($sal->transport ?? 0) + ($sal->phone_bill ?? 0) + ($sal->internet_bill ?? 0) +
                    ($sal->special ?? 0) + ($sal->overtime_pay ?? 0);
                @endphp
                <div class="emp-salary-total bg-success bg-opacity-10 mt-2">
                  <span class="fw-bold text-success">Total Earnings</span>
                  <span class="fw-bold text-success fs-5">৳ {{ number_format($grossEarnings, 2) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-circle-minus me-2 text-danger"></i>Deductions
              </div>
              <div class="card-body">
                <div class="emp-salary-row"><span>Provident Fund</span><span>৳ {{ number_format($sal->provident_fund ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Income Tax</span><span>৳ {{ number_format($sal->income_tax ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Health Insurance</span><span>৳ {{ number_format($sal->health_insurance ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Life Insurance</span><span>৳ {{ number_format($sal->life_insurance ?? 0, 2) }}</span></div>
                @php
                  $totalDeductions = ($sal->provident_fund ?? 0) + ($sal->income_tax ?? 0) +
                    ($sal->health_insurance ?? 0) + ($sal->life_insurance ?? 0);
                  $netSalary = $grossEarnings - $totalDeductions;
                @endphp
                <div class="emp-salary-total bg-danger bg-opacity-10 mt-2">
                  <span class="fw-bold text-danger">Total Deductions</span>
                  <span class="fw-bold text-danger fs-5">৳ {{ number_format($totalDeductions, 2) }}</span>
                </div>
                <div class="emp-net-salary-box mt-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-6">Net Take-Home Salary</span>
                    <span class="fw-bold fs-4 text-primary">৳ {{ number_format($netSalary, 2) }}</span>
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
            <p class="mb-3">No salary structure defined for this employee yet.</p>
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id)) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-plus me-1"></i> Add Salary Structure
            </a>
          </div>
        </div>
        @endif
      </div>

      {{-- ──────── TAB 4: PAYSLIP ──────── --}}
      <div class="tab-pane fade" id="pane-payslip" role="tabpanel">
        @if($employee->payrolls->count() > 0)
        @php
          $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                     7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
          $payrolls = $employee->payrolls->sortByDesc(fn($p) => ($p->year * 100) + $p->month);
        @endphp
        <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1 text-info"></i>{{ $employee->payrolls->count() }} payroll record(s). Click "Print Payslip" to generate a printable payslip for that month.</p>
        <div class="row g-3">
          @foreach($payrolls as $payroll)
          <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm emp-payslip-card">
              <div class="card-header emp-payslip-header">
                <div>
                  <div class="fw-bold">{{ $months[$payroll->month] ?? 'Month '.$payroll->month }} {{ $payroll->year }}</div>
                  <div class="small opacity-75">Payroll Period</div>
                </div>
                <span class="emp-payslip-badge"><i class="fas fa-file-invoice-dollar me-1"></i>Payslip</span>
              </div>
              <div class="card-body">
                <div class="emp-payslip-row"><span>Basic</span><span>৳ {{ number_format($payroll->basic ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>House Rent</span><span>৳ {{ number_format($payroll->house_rent ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Medical</span><span>৳ {{ number_format($payroll->medical ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Gross Salary</span><span class="fw-semibold text-success">৳ {{ number_format($payroll->gross_salary ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Deductions</span><span class="text-danger">− ৳ {{ number_format($payroll->deduction ?? 0, 2) }}</span></div>
                @if($payroll->days_present || $payroll->days_absent)
                <div class="emp-payslip-row"><span>Days Present</span><span class="badge bg-success">{{ $payroll->days_present ?? 0 }} days</span></div>
                <div class="emp-payslip-row"><span>Days Absent</span><span class="badge bg-danger">{{ $payroll->days_absent ?? 0 }} days</span></div>
                @endif
                <div class="emp-payslip-net">
                  <span>Net Salary</span>
                  <span class="emp-payslip-net-amount">৳ {{ number_format($payroll->net_salary ?? 0, 2) }}</span>
                </div>
              </div>
              <div class="card-footer bg-transparent border-0 pt-0">
                <button
                  onclick="printPayslip({{ $payroll->id }}, '{{ $employee->firstname }} {{ $employee->lastname }}', '{{ $employee->unique_id }}', '{{ optional($employee->department)->title }}', '{{ optional($employee->designation)->title }}', '{{ $months[$payroll->month] ?? $payroll->month }} {{ $payroll->year }}', {{ $payroll->basic ?? 0 }}, {{ $payroll->house_rent ?? 0 }}, {{ $payroll->medical ?? 0 }}, {{ $payroll->gross_salary ?? 0 }}, {{ $payroll->deduction ?? 0 }}, {{ $payroll->net_salary ?? 0 }}, {{ $payroll->days_present ?? 0 }}, {{ $payroll->days_absent ?? 0 }})"
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

      {{-- ──────── TAB 5: LEAVE HISTORY ──────── --}}
      <div class="tab-pane fade" id="pane-leaves" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-person-walking-arrow-right me-2 text-warning"></i>Leave History</span>
            <span class="badge bg-warning text-dark">{{ $employee->leaves->count() }} records</span>
          </div>
          @if($employee->leaves->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
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
                      @else —
                      @endif
                    </td>
                    <td>{{ Str::limit($leave->reason ?? '—', 40) }}</td>
                    <td>
                      @php $lstatus = $leave->status ?? 'pending'; @endphp
                      @if($lstatus == 'approved' || $lstatus == 1)
                        <span class="badge bg-success">Approved</span>
                      @elseif($lstatus == 'rejected' || $lstatus == 2)
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

@endsection

@section('script')
<style>
/* ── Hero ── */
.emp-profile-hero { border-radius: 16px; overflow: hidden; }
.emp-hero-banner {
  height: 90px;
  background: linear-gradient(135deg, #3b7ddd 0%, #1cbb8c 100%);
}
.emp-hero-body { padding: 0 20px 20px; margin-top: -30px; }
.emp-avatar-wrap { flex-shrink: 0; }
.emp-avatar {
  width: 78px; height: 78px; border-radius: 50%;
  background: linear-gradient(135deg, #3b7ddd, #1cbb8c);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.5rem; font-weight: 800;
  border: 4px solid #fff; box-shadow: 0 4px 16px rgba(59,125,221,.3);
}
.emp-hero-name { font-size: 1.5rem; font-weight: 700; }
.emp-status-badge { font-size: .7rem; }
.emp-hero-meta { font-size: .875rem; }

/* ── Stat pills ── */
.emp-stat-pills { margin-top: 8px; }
.emp-stat-pill {
  display: flex; flex-direction: column; align-items: center;
  background: rgba(59,125,221,.08);
  border: 1px solid rgba(59,125,221,.15);
  border-radius: 10px; padding: 8px 16px; min-width: 64px;
}
.emp-stat-num { font-size: 1.3rem; font-weight: 800; color: #3b7ddd; line-height: 1; }
.emp-stat-label { font-size: .68rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }

/* ── Tabs ── */
.emp-tab-nav {
  display: flex; gap: 4px;
  border-bottom: 2px solid #e0e6ed;
  flex-wrap: wrap;
}
.emp-tab-btn {
  background: none; border: none;
  padding: 10px 18px; font-size: .9rem; font-weight: 500;
  color: #6c757d; border-radius: 8px 8px 0 0;
  cursor: pointer; transition: color .2s, background .2s;
  display: flex; align-items: center; gap: 4px;
}
.emp-tab-btn:hover { color: #3b7ddd; background: #f0f4ff; }
.emp-tab-btn.active {
  color: #3b7ddd; background: #fff;
  border-bottom: 2px solid #3b7ddd; margin-bottom: -2px;
}
.emp-tab-count {
  background: #3b7ddd; color: #fff;
  font-size: .68rem; font-weight: 700;
  border-radius: 10px; padding: 1px 6px; line-height: 1.4;
}

/* ── Section headers ── */
.emp-section-header {
  background: #f8f9fa; border-bottom: 1px solid #e9ecef;
  font-weight: 600; font-size: .9rem; padding: 12px 16px;
}

/* ── Info grid ── */
.emp-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.emp-info-item { padding: 10px 4px; border-bottom: 1px solid #f1f3f5; display: flex; flex-direction: column; gap: 2px; }
.emp-info-full { grid-column: 1 / -1; }
.emp-info-label { font-size: .72rem; color: #6c757d; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
.emp-info-value { font-size: .9rem; color: #212529; }

/* ── Salary ── */
.emp-salary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f3f5; font-size: .9rem; }
.emp-salary-total { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-radius: 10px; }
.emp-net-salary-box { background: linear-gradient(135deg,#3b7ddd15,#1cbb8c15); border: 1px solid #3b7ddd30; border-radius: 12px; padding: 16px; }

/* ── Payslip cards ── */
.emp-payslip-card { border-radius: 14px; transition: transform .2s, box-shadow .2s; }
.emp-payslip-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
.emp-payslip-header {
  display: flex; justify-content: space-between; align-items: center;
  background: linear-gradient(135deg, #3b7ddd, #1cbb8c);
  color: #fff; border-radius: 14px 14px 0 0 !important; padding: 14px 16px;
}
.emp-payslip-badge { background: rgba(255,255,255,.2); border-radius: 20px; padding: 4px 10px; font-size: .75rem; }
.emp-payslip-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f5f5f5; font-size: .875rem; }
.emp-payslip-net { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg,#3b7ddd10,#1cbb8c10); border-radius: 10px; padding: 10px 12px; margin-top: 8px; font-weight: 600; }
.emp-payslip-net-amount { color: #1cbb8c; font-size: 1.15rem; font-weight: 700; }

/* ── Print ── */
@media print {
  #sidebar, .main > nav, .footer, .emp-hero-actions, .emp-stat-pills,
  .emp-tab-nav, .tab-pane:not(.active) { display: none !important; }
  .tab-pane.active { display: block !important; }
}
</style>

<script>
// Tab switching
document.querySelectorAll('.emp-tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.emp-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('show','active'));
    this.classList.add('active');
    const t = document.querySelector(this.getAttribute('data-bs-target'));
    if (t) t.classList.add('show','active');
  });
});

// Print Payslip
function printPayslip(id, name, empId, dept, desig, period, basic, houseRent, medical, gross, deduction, net, present, absent) {
  const fmt = n => parseFloat(n).toLocaleString('en-US', {minimumFractionDigits:2});
  const html = `<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="UTF-8"/>
  <title>Payslip — ${name} — ${period}</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f7fa;padding:30px}
    .payslip{max-width:700px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1)}
    .ps-header{background:linear-gradient(135deg,#3b7ddd,#1cbb8c);color:#fff;padding:28px 32px}
    .ps-header h2{font-size:1.5rem;margin-bottom:4px}.ps-header p{opacity:.85;font-size:.9rem}
    .ps-badge{background:rgba(255,255,255,.2);padding:4px 12px;border-radius:20px;font-size:.8rem;display:inline-block;margin-top:8px}
    .ps-body{padding:28px 32px}
    .ps-emp-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;background:#f8f9fa;border-radius:10px;padding:16px}
    .ps-emp-label{font-size:.72rem;color:#6c757d;text-transform:uppercase;letter-spacing:.05em}
    .ps-emp-value{font-size:.9rem;font-weight:600;color:#212529;margin-top:2px}
    .ps-section{margin-bottom:20px}
    .ps-section-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6c757d;border-bottom:1px solid #e9ecef;padding-bottom:6px;margin-bottom:10px}
    .ps-row{display:flex;justify-content:space-between;padding:6px 0;font-size:.88rem;border-bottom:1px dashed #f0f0f0}
    .ps-total{display:flex;justify-content:space-between;padding:10px 14px;border-radius:8px;font-weight:700;margin-top:6px}
    .earn{background:#d4edda;color:#155724}.ded{background:#f8d7da;color:#721c24}
    .ps-net{background:linear-gradient(135deg,#3b7ddd,#1cbb8c);color:#fff;padding:18px 24px;border-radius:12px;margin-top:20px;display:flex;justify-content:space-between;align-items:center}
    .ps-net-label{font-size:1rem;font-weight:600}.ps-net-amount{font-size:2rem;font-weight:800}
    .ps-footer{text-align:center;padding:16px;font-size:.78rem;color:#6c757d;border-top:1px solid #eee}
    @media print{body{padding:10px;background:#fff}}
  </style>
</head><body>
<div class="payslip">
  <div class="ps-header">
    <h2>PAYSLIP</h2><p>Pay Period: ${period}</p>
    <span class="ps-badge">Document No. PS-${id}</span>
  </div>
  <div class="ps-body">
    <div class="ps-emp-grid">
      <div><div class="ps-emp-label">Employee Name</div><div class="ps-emp-value">${name}</div></div>
      <div><div class="ps-emp-label">Employee ID</div><div class="ps-emp-value">${empId}</div></div>
      <div><div class="ps-emp-label">Department</div><div class="ps-emp-value">${dept}</div></div>
      <div><div class="ps-emp-label">Designation</div><div class="ps-emp-value">${desig}</div></div>
      <div><div class="ps-emp-label">Days Present</div><div class="ps-emp-value">${present} days</div></div>
      <div><div class="ps-emp-label">Days Absent</div><div class="ps-emp-value">${absent} days</div></div>
    </div>
    <div class="ps-section">
      <div class="ps-section-title">Earnings</div>
      <div class="ps-row"><span>Basic Salary</span><span>৳ ${fmt(basic)}</span></div>
      <div class="ps-row"><span>House Rent Allowance</span><span>৳ ${fmt(houseRent)}</span></div>
      <div class="ps-row"><span>Medical Allowance</span><span>৳ ${fmt(medical)}</span></div>
      <div class="ps-total earn"><span>Gross Salary</span><span>৳ ${fmt(gross)}</span></div>
    </div>
    <div class="ps-section">
      <div class="ps-section-title">Deductions</div>
      <div class="ps-row"><span>Total Deductions</span><span>− ৳ ${fmt(deduction)}</span></div>
      <div class="ps-total ded"><span>Total Deducted</span><span>− ৳ ${fmt(deduction)}</span></div>
    </div>
    <div class="ps-net">
      <div class="ps-net-label">Net Take-Home Salary</div>
      <div class="ps-net-amount">৳ ${fmt(net)}</div>
    </div>
  </div>
  <div class="ps-footer">System-generated payslip &bull; ${new Date().toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'})}</div>
</div>
<script>window.onload=function(){window.print();}<\/script>
</body></html>`;
  const w = window.open('','_blank','width=800,height=700');
  w.document.write(html);
  w.document.close();
}
</script>
@endsection
