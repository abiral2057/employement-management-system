@extends('layouts.admin')

@section('title')
  {{ $employee->firstname }} {{ $employee->lastname }} — Employee Profile 360
@endsection

@section('header')
  <div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
      <h1 class="h1 mb-0">
        <i class="fas fa-id-card text-dark me-2"></i>{{ $employee->firstname }} {{ $employee->lastname }} <span class="badge badge-yellowgreen ms-2 fs-6">Profile 360</span>
      </h1>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#profileAdvanceModal">
        <i class="fa-solid fa-hand-holding-dollar me-1"></i> {{ __('Pay in Advance / Overtime') }}
      </button>
      <a href="{{ route('payroll.transactions', ['employee_id' => $employee->id]) }}" class="btn btn-outline-dark fw-bold shadow-sm">
        <i class="fa-solid fa-receipt me-1"></i> {{ __('Transaction History') }}
      </a>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}" class="btn btn-outline-primary">
        <i class="fas fa-search me-1"></i> Search
      </a>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id)) }}" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i> Edit Profile
      </a>
      <button onclick="window.print()" class="btn btn-outline-secondary">
        <i class="fas fa-print me-1"></i> Print
      </button>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : route('hr.employee.index')) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
      </a>
    </div>
  </div>
@endsection

@section('content')

@php
    $totalOtHours = 0;
    foreach($employee->overTime as $ot) {
        $totalOtHours += $ot->hours_decimal;
    }
    $totalBalanceDue = 0;
    foreach($employee->payrolls as $p) {
        $totalBalanceDue += $p->balance_due;
    }
@endphp

{{-- ── Profile Hero Card ── --}}
<section class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm emp-profile-hero">
      <div class="emp-hero-banner"></div>
      <div class="card-body pt-0">
        <div class="d-flex flex-wrap align-items-end gap-3 emp-hero-body">
          {{-- Avatar --}}
          <div class="emp-avatar-wrap">
            @if($employee->avatar && file_exists(public_path($employee->avatar)))
              <img src="{{ asset($employee->avatar) }}" alt="{{ $employee->firstname }}" class="emp-avatar rounded-circle border shadow-sm" style="object-fit: cover; width: 78px; height: 78px;" />
            @else
              <div class="emp-avatar">
                {{ strtoupper(substr($employee->firstname, 0, 1)) }}{{ strtoupper(substr($employee->lastname, 0, 1)) }}
              </div>
            @endif
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

              @if($totalBalanceDue > 0)
                <span class="badge bg-danger emp-status-badge pulse-badge"><i class="fa-solid fa-bell me-1"></i>Payment Due: € {{ number_format($totalBalanceDue, 2) }}</span>
              @endif
            </div>
            <div class="d-flex flex-wrap gap-3 emp-hero-meta text-muted">
              <span><i class="fa-solid fa-id-badge me-1 text-primary"></i><strong>{{ $employee->unique_id }}</strong></span>
              <span><i class="fa-solid fa-building me-1 text-info"></i>{{ optional($employee->department)->title ?? '—' }}</span>
              <span><i class="fa-solid fa-briefcase me-1 text-warning"></i>{{ optional($employee->designation)->title ?? '—' }}</span>
              <span>
                <i class="fa-solid fa-clock me-1 text-purple" style="color:#9333ea;"></i>
                {{ optional($employee->schedule)->title ?? 'Shift' }}
                @if(optional($employee->schedule)->isNightShift())
                    <span class="badge px-1" style="background:#f3e8ff;color:#9333ea;">Night</span>
                @endif
              </span>
              @if($employee->email)
              <span><i class="fas fa-envelope me-1 text-success"></i>{{ $employee->email }}</span>
              @endif
              @if($employee->phone)
              <span><i class="fas fa-phone me-1 text-secondary"></i>{{ $employee->phone }}</span>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── Tab Navigation & Content Layout ── --}}
<section class="row g-4 mb-4">
  {{-- ── Vertical Scrollable Tablist Sidebar ── --}}
  <div class="col-lg-3 col-md-4">
    <div class="card border-0 shadow-sm sticky-top emp-tab-sidebar" style="top: 20px;">
      <div class="card-header bg-white border-bottom py-3 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-dark text-uppercase small" style="letter-spacing: .05em;">
          <i class="fa-solid fa-layer-group me-2 text-primary"></i>Profile Sections
        </span>
        <span class="badge bg-light text-dark border">10 Tabs</span>
      </div>
      <div class="card-body p-2 emp-tab-scroll-wrap">
        <div class="nav flex-column emp-tab-nav-vertical gap-1" id="profileTabs" role="tablist" aria-orientation="vertical">
          <button class="emp-tab-btn active" data-bs-toggle="tab" data-bs-target="#pane-profile" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fas fa-id-card text-muted"></i>Personal Profile</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-attendance" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fas fa-calendar-check text-muted"></i>Attendance</span>
            <span class="emp-tab-count">{{ $employee->attendances->count() }}</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-shifts" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-business-time text-muted"></i>Shift History</span>
            <span class="emp-tab-count" style="background:#f3e8ff;color:#9333ea;">{{ $employee->shiftRosters->count() }}</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-overtime" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-stopwatch text-muted"></i>Overtime (OT)</span>
            <span class="emp-tab-count" style="background:#fef3c7;color:#b45309;">{{ $employee->overTime->count() }}</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-payment-due" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-file-invoice-dollar text-muted"></i>Payment & Due</span>
            @if($totalBalanceDue > 0)
              <span class="emp-tab-count bg-danger text-white">Due</span>
            @else
              <span class="emp-tab-count bg-success text-white">OK</span>
            @endif
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-salary" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fas fa-money-bill-wave text-muted"></i>Salary Structure</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-payslip" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fas fa-file-invoice text-muted"></i>Payslips</span>
            <span class="emp-tab-count">{{ $employee->payrolls->count() }}</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-leaves" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fas fa-person-walking-arrow-right text-muted"></i>Leave History</span>
            <span class="emp-tab-count">{{ $employee->leaves->count() }}</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-activity" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-timeline text-muted"></i>Activity History</span>
            <span class="emp-tab-count bg-secondary text-white">{{ $employee->histories->count() }}</span>
          </button>
          <button class="emp-tab-btn" data-bs-toggle="tab" data-bs-target="#pane-documents" type="button" role="tab">
            <span class="d-flex align-items-center gap-2"><i class="fa-solid fa-folder-open text-muted"></i>Documents</span>
            <span class="emp-tab-count bg-info text-white">{{ $employee->documents->count() }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Main Tab Content ── --}}
  <div class="col-lg-9 col-md-8">
    <div class="tab-content" id="profileTabsContent">

      {{-- ──────── TAB 1: PROFILE ──────── --}}
      <div class="tab-pane fade show active" id="pane-profile" role="tabpanel">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-user me-2 text-primary"></i>Personal Details
              </div>
              <div class="card-body">
                <div class="emp-info-grid">
                  <div class="emp-info-item">
                    <span class="emp-info-label">Date of Birth</span>
                    <span class="emp-info-value">{{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('M d, Y') : '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Gender</span>
                    <span class="emp-info-value">
                      @if($employee->gender == 0 || $employee->gender == 1) Male
                      @elseif($employee->gender == 2) Female
                      @else {{ $employee->gender ?? '—' }}
                      @endif
                    </span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Marital Status</span>
                    <span class="emp-info-value">{{ ucfirst($employee->marital ?? '—') }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Religion</span>
                    <span class="emp-info-value">{{ $employee->religion ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Emergency Contact</span>
                    <span class="emp-info-value">{{ $employee->emergency_contact_name ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Emergency Phone</span>
                    <span class="emp-info-value">{{ $employee->emergency_contact_phone ?? '—' }}</span>
                  </div>
                  <div class="emp-info-item emp-info-full">
                    <span class="emp-info-label">Residential Address</span>
                    <span class="emp-info-value">{{ $employee->address ?? '—' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fas fa-briefcase me-2 text-warning"></i>Employment & Shift Profile
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
                    <span class="emp-info-label">Employment Type</span>
                    <span class="emp-info-value"><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $employee->employment_type ?? 'Full-Time')) }}</span></span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Default Shift</span>
                    <span class="emp-info-value fw-semibold text-primary">
                        {{ optional($employee->schedule)->title ?? 'Standard Shift' }}
                        @if(optional($employee->schedule)->isNightShift())
                            <span class="badge px-1" style="background:#f3e8ff;color:#9333ea;">🌙 Night Shift</span>
                        @endif
                    </span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Shift Hours</span>
                    <span class="emp-info-value">
                        {{ optional($employee->schedule)->time_in ? date('g:i A', strtotime($employee->schedule->time_in)) : '—' }} &rarr;
                        {{ optional($employee->schedule)->time_out ? date('g:i A', strtotime($employee->schedule->time_out)) : '—' }}
                        @if(optional($employee->schedule)->cross_midnight || (optional($employee->schedule)->time_out < optional($employee->schedule)->time_in))
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">+1d</span>
                        @endif
                    </span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Date Joined</span>
                    <span class="emp-info-value text-success fw-semibold">{{ $employee->created_at->format('d M Y') }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Tenure</span>
                    <span class="emp-info-value">{{ $employee->created_at->diffForHumans() }}</span>
                  </div>
                  <div class="emp-info-item">
                    <span class="emp-info-label">Hourly Overtime Rate</span>
                    <span class="emp-info-value fw-semibold text-success">
                        € {{ number_format(optional($employee->salary)->overtime_rate ?? 0, 2) }}/hr
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ──────── TAB 2: ATTENDANCE (BUG FIXED) ──────── --}}
      <div class="tab-pane fade" id="pane-attendance" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-calendar-check me-2 text-info"></i>Daily Attendance Records</span>
            <span class="badge bg-info text-dark">{{ $employee->attendances->count() }} records</span>
          </div>
          @if($employee->attendances->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Check In Time</th>
                    <th>Check Out Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->attendances->sortByDesc('attendance_date') as $i => $att)
                  @php
                    $attDate = $att->attendance_date ?? $att->date;
                    $depart = $employee->departs->firstWhere('depart_date', $attDate);
                  @endphp
                  <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>
                        <span class="fw-semibold">{{ $attDate ? \Carbon\Carbon::parse($attDate)->format('d M Y') : '—' }}</span>
                        <div class="small text-muted">{{ $attDate ? \Carbon\Carbon::parse($attDate)->format('l') : '' }}</div>
                    </td>
                    <td>
                        <span class="badge bg-success bg-opacity-10 text-success fs-6">
                            <i class="fa-regular fa-clock me-1"></i>{{ $att->attendance_time ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($depart && $depart->depart_time)
                            <span class="badge bg-info bg-opacity-10 text-info fs-6">
                                <i class="fa-regular fa-clock me-1"></i>{{ $depart->depart_time }}
                            </span>
                        @else
                            <span class="text-muted">Not checked out</span>
                        @endif
                    </td>
                    <td>
                      @if($att->status == 1)
                        <span class="badge bg-success">Present</span>
                      @elseif($att->status === 0 || $att->status === '0')
                        <span class="badge bg-danger">Absent</span>
                      @else
                        <span class="badge bg-success">Recorded</span>
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
            <i class="fas fa-calendar-xmark fa-3x mb-3 opacity-25"></i>
            <p>No attendance records found for this employee.</p>
          </div>
          @endif
        </div>
      </div>

      {{-- ──────── TAB 3: SHIFT HISTORY (NEW) ──────── --}}
      <div class="tab-pane fade" id="pane-shifts" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-business-time me-2 text-purple" style="color:#9333ea;"></i>Day / Night / Extra Shift Roster History</span>
            <div class="d-flex gap-2">
                <span class="badge" style="background:#f3e8ff;color:#9333ea;">{{ $employee->shiftRosters->count() }} assignments</span>
                <a href="{{ route('shifts.roster') }}?employee_id={{ $employee->id }}" class="btn btn-outline-primary btn-sm py-0">Open in Roster</a>
            </div>
          </div>
          @if($employee->shiftRosters->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                  <tr>
                    <th>#</th>
                    <th>Shift Date</th>
                    <th>Shift Type</th>
                    <th>Shift Schedule</th>
                    <th>Hours</th>
                    <th>Extra Duty Allowance</th>
                    <th>Status</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->shiftRosters->sortByDesc('shift_date') as $i => $sr)
                  <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($sr->shift_date)->format('d M Y') }}</strong>
                        <div class="small text-muted">{{ \Carbon\Carbon::parse($sr->shift_date)->format('l') }}</div>
                    </td>
                    <td>
                      @if($sr->shift_type === 'day')
                        <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1"><i class="fa-solid fa-sun me-1"></i>Day Shift</span>
                      @elseif($sr->shift_type === 'night')
                        <span class="badge px-2 py-1" style="background:#f3e8ff;color:#7e22ce;border:1px solid #d8b4fe;"><i class="fa-solid fa-moon me-1"></i>Night Shift</span>
                      @else
                        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1"><i class="fa-solid fa-bolt me-1"></i>Extra Shift</span>
                      @endif
                    </td>
                    <td>
                      <span class="fw-semibold">{{ optional($sr->schedule)->title ?? 'Shift' }}</span>
                      <div class="small text-muted">{{ optional($sr->schedule)->time_in ? date('g:i A', strtotime($sr->schedule->time_in)) : '' }} - {{ optional($sr->schedule)->time_out ? date('g:i A', strtotime($sr->schedule->time_out)) : '' }}</div>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ $sr->hours }} hrs</span></td>
                    <td>
                      @if($sr->extra_allowance > 0)
                        <strong class="text-success">€ {{ number_format($sr->extra_allowance, 2) }}</strong>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-light text-dark text-capitalize border">{{ $sr->status }}</span>
                    </td>
                    <td class="small text-muted">{{ $sr->notes ?? '—' }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @else
          <div class="card-body text-center py-5 text-muted">
            <i class="fa-solid fa-business-time fa-3x mb-3 opacity-25"></i>
            <p class="mb-2">No shift roster entries logged yet. Primary default shift: <strong>{{ optional($employee->schedule)->title ?? 'Standard Shift' }}</strong>.</p>
            <a href="{{ route('shifts.roster') }}" class="btn btn-outline-primary btn-sm">Assign Day/Night/Extra Shift in Roster</a>
          </div>
          @endif
        </div>
      </div>

      {{-- ──────── TAB 4: OVERTIME (OT) HISTORY (NEW) ──────── --}}
      <div class="tab-pane fade" id="pane-overtime" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-stopwatch me-2 text-warning"></i>Overtime (OT) History & Claims</span>
            <span class="badge bg-warning text-dark">{{ $employee->overTime->count() }} records &bull; {{ round($totalOtHours, 1) }} total hrs</span>
          </div>
          @if($employee->overTime->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>OT Duration</th>
                    <th>Hourly Rate</th>
                    <th>Total Payout</th>
                    <th>Reason / Activity</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->overTime->sortByDesc('overtime_date') as $i => $ot)
                  <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($ot->overtime_date)->format('d M Y') }}</strong>
                        <div class="small text-muted">{{ \Carbon\Carbon::parse($ot->overtime_date)->format('D') }}</div>
                    </td>
                    <td>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 fs-6">
                            <i class="fa-regular fa-clock me-1"></i>{{ $ot->overtime_duration }}
                        </span>
                        <div class="small text-muted">({{ $ot->hours_decimal }} hrs)</div>
                    </td>
                    <td>€ {{ number_format($ot->rate ?? 0, 2) }}/hr</td>
                    <td><strong class="text-success fs-6">€ {{ number_format($ot->amount ?? 0, 2) }}</strong></td>
                    <td class="small text-muted">{{ $ot->reason ?? 'Extra Duty Overtime' }}</td>
                    <td>
                        @if($ot->status === 'approved')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="fa-solid fa-check me-1"></i>Approved</span>
                        @elseif($ot->status === 'rejected')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1"><i class="fa-solid fa-xmark me-1"></i>Rejected</span>
                        @else
                            <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1">Pending</span>
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
            <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 opacity-25"></i>
            <p class="mb-2">No overtime logged for this employee yet.</p>
            <a href="{{ route('attendance.overtime') }}" class="btn btn-outline-primary btn-sm">Log Overtime in OT Manager</a>
          </div>
          @endif
        </div>
      </div>

      {{-- ──────── TAB 5: PAYMENT DUE & PAYROLL HISTORY (NEW) ──────── --}}
      <div class="tab-pane fade" id="pane-payment-due" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span><i class="fa-solid fa-file-invoice-dollar me-2 text-success"></i>Monthly Salary Payment & Outstanding Due Statement</span>
            <div>
                @if($totalBalanceDue > 0)
                    <span class="badge bg-danger">Outstanding Balance: € {{ number_format($totalBalanceDue, 2) }}</span>
                @else
                    <span class="badge bg-success">All Salaries Settled</span>
                @endif
            </div>
          </div>
          @if($employee->payrolls->count() > 0)
          @php
            $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
          @endphp
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                  <tr>
                    <th>Payroll Period</th>
                    <th>Net Salary</th>
                    <th>Amount Paid</th>
                    <th>Balance Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Reminders</th>
                    <th class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->payrolls->sortByDesc(fn($p) => ($p->year * 100) + $p->month) as $p)
                  @php $isOverdue = $p->isOverdue(); @endphp
                  <tr class="{{ $isOverdue ? 'table-danger table-opacity-10' : '' }}">
                    <td>
                        <strong class="text-dark">{{ $months[$p->month] ?? $p->month }} {{ $p->year }}</strong>
                    </td>
                    <td><strong>€ {{ number_format($p->net_salary, 2) }}</strong></td>
                    <td>
                        @if($p->paid_amount > 0)
                            <span class="text-success fw-semibold">€ {{ number_format($p->paid_amount, 2) }}</span>
                            @if($p->paid_date)
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($p->paid_date)->format('d M Y') }}</div>
                            @endif
                        @else
                            <span class="text-muted">€ 0.00</span>
                        @endif
                    </td>
                    <td>
                        @if($p->balance_due > 0)
                            <strong class="text-danger fs-6">€ {{ number_format($p->balance_due, 2) }}</strong>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-check me-1"></i>Settled</span>
                        @endif
                    </td>
                    <td>
                        @if($p->due_date)
                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                {{ \Carbon\Carbon::parse($p->due_date)->format('d M Y') }}
                            </span>
                            @if($isOverdue)
                                <div class="small text-danger fw-bold">Overdue!</div>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->payment_status === 'paid')
                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">Paid</span>
                        @elseif($isOverdue)
                            <span class="badge bg-danger text-white px-2 py-1">Overdue</span>
                        @elseif($p->payment_status === 'partial')
                            <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1">Partial</span>
                        @else
                            <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        @if($p->reminder_count > 0)
                            <span class="badge bg-light text-dark border"><i class="fa-solid fa-bell text-warning me-1"></i>{{ $p->reminder_count }} sent</span>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($p->balance_due > 0)
                            <a href="{{ route('payroll.due') }}?year={{ $p->year }}&month={{ $p->month }}" class="btn btn-success btn-sm py-0">
                                Settle Due
                            </a>
                        @else
                            <span class="badge bg-light text-success border">Paid</span>
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
            <i class="fa-solid fa-wallet fa-3x mb-3 opacity-25"></i>
            <p>No payroll generated for this employee yet.</p>
          </div>
          @endif
        </div>
      </div>

      {{-- ──────── TAB 6: SALARY STRUCTURE ──────── --}}
      <div class="tab-pane fade" id="pane-salary" role="tabpanel">
        @if($employee->salary)
        @php $sal = $employee->salary; @endphp
        <div class="row g-3">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-circle-plus me-2 text-success"></i>Earnings / Allowances</span>
                <div class="d-flex gap-1 flex-wrap">
                  <button type="button" class="btn btn-success btn-sm py-1 px-2" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#profileAdvanceModal">
                    <i class="fa-solid fa-hand-holding-dollar me-1"></i>Pay in Advance
                  </button>
                  <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id)) }}" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.8rem;">
                    <i class="fas fa-pen-to-square me-1"></i>Edit
                  </a>
                </div>
              </div>
              <div class="card-body">
                <div class="emp-salary-row"><span>Basic Salary</span><span class="fw-semibold">€ {{ number_format($sal->basic ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>House Rent</span><span>€ {{ number_format($sal->house_rent ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Medical Allowance</span><span>€ {{ number_format($sal->medical ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Transport Allowance</span><span>€ {{ number_format($sal->transport ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Phone Bill</span><span>€ {{ number_format($sal->phone_bill ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Internet Bill</span><span>€ {{ number_format($sal->internet_bill ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Special Allowance</span><span>€ {{ number_format($sal->special ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Overtime Rate (hourly)</span><span class="fw-bold text-success">€ {{ number_format($sal->overtime_rate ?? 0, 2) }}/hr</span></div>
                @php
                  $grossEarnings = ($sal->basic ?? 0) + ($sal->house_rent ?? 0) + ($sal->medical ?? 0) +
                    ($sal->transport ?? 0) + ($sal->phone_bill ?? 0) + ($sal->internet_bill ?? 0) +
                    ($sal->special ?? 0);
                @endphp
                <div class="emp-salary-total bg-success bg-opacity-10 mt-2">
                  <span class="fw-bold text-success">Standard Monthly Earnings</span>
                  <span class="fw-bold text-success fs-5">€ {{ number_format($grossEarnings, 2) }}</span>
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
                <div class="emp-salary-row"><span>Provident Fund</span><span>€ {{ number_format($sal->provident_fund ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Income Tax</span><span>€ {{ number_format($sal->income_tax ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Health Insurance</span><span>€ {{ number_format($sal->health_insurance ?? 0, 2) }}</span></div>
                <div class="emp-salary-row"><span>Life Insurance</span><span>€ {{ number_format($sal->life_insurance ?? 0, 2) }}</span></div>
                @php
                  $totalDeductions = ($sal->provident_fund ?? 0) + ($sal->income_tax ?? 0) +
                    ($sal->health_insurance ?? 0) + ($sal->life_insurance ?? 0);
                  $netSalary = $grossEarnings - $totalDeductions;
                @endphp
                <div class="emp-salary-total bg-danger bg-opacity-10 mt-2">
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
            <p class="mb-3">No salary structure defined for this employee yet.</p>
            <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id)) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-plus me-1"></i> Add Salary Structure
            </a>
          </div>
        </div>
        @endif
      </div>

      {{-- ──────── TAB 7: PAYSLIP ──────── --}}
      <div class="tab-pane fade" id="pane-payslip" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <div>
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i>Historical Payslips & Salary Slips</h5>
            <small class="text-muted">Generated monthly payslips for this employee.</small>
          </div>
        </div>
        @if($employee->payrolls->count() > 0)
        @php
          $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
                     7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
          $payrolls = $employee->payrolls->sortByDesc(fn($p) => ($p->year * 100) + $p->month);
        @endphp
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
                <div class="emp-payslip-row"><span>Basic</span><span>€ {{ number_format($payroll->basic ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Overtime Pay</span><span class="text-primary">+ € {{ number_format($payroll->overtime_pay ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Gross Salary</span><span class="fw-semibold text-success">€ {{ number_format($payroll->gross_salary ?? 0, 2) }}</span></div>
                <div class="emp-payslip-row"><span>Deductions</span><span class="text-danger">− € {{ number_format($payroll->deduction ?? 0, 2) }}</span></div>
                <div class="emp-payslip-net">
                  <span>Net Salary</span>
                  <span class="emp-payslip-net-amount">€ {{ number_format($payroll->net_salary ?? 0, 2) }}</span>
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

      {{-- ──────── TAB 8: LEAVE HISTORY (BUG FIXED) ──────── --}}
      <div class="tab-pane fade" id="pane-leaves" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fas fa-person-walking-arrow-right me-2 text-warning"></i>Leave History</span>
            <span class="badge bg-warning text-dark">{{ $employee->leaves->count() }} records</span>
          </div>
          @if($employee->leaves->count() > 0)
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small text-muted">
                  <tr>
                    <th>#</th>
                    <th>Leave Title</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($employee->leaves->sortByDesc('start_date') as $i => $leave)
                  @php
                    $sDate = $leave->start_date ?? $leave->from_date;
                    $eDate = $leave->end_date ?? $leave->to_date;
                    $days = ($sDate && $eDate) ? \Carbon\Carbon::parse($sDate)->diffInDays(\Carbon\Carbon::parse($eDate)) + 1 : 1;
                  @endphp
                  <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td><strong>{{ $leave->title ?? 'Leave' }}</strong></td>
                    <td>{{ $sDate ? \Carbon\Carbon::parse($sDate)->format('d M Y') : '—' }}</td>
                    <td>{{ $eDate ? \Carbon\Carbon::parse($eDate)->format('d M Y') : '—' }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $days }} days</span></td>
                    <td>{{ Str::limit($leave->leave_reason ?? $leave->reason ?? '—', 35) }}</td>
                    <td>
                      @if($leave->status == 1 || $leave->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                      @elseif($leave->status == 2 || $leave->status === 'rejected')
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

      {{-- ──────── TAB 9: ACTIVITY & AUDIT HISTORY (NEW) ──────── --}}
      <div class="tab-pane fade" id="pane-activity" role="tabpanel">
        <div class="card border-0 shadow-sm">
          <div class="card-header emp-section-header d-flex align-items-center justify-content-between">
            <span><i class="fa-solid fa-timeline me-2 text-primary"></i>Employment & Activity Lifecycle History</span>
            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $employee->histories->count() }} events logged</span>
          </div>
          @if($employee->histories->count() > 0)
          <div class="card-body p-4">
            <div class="timeline">
              @foreach($employee->histories as $hist)
              <div class="timeline-item pb-4 position-relative ps-4 border-start border-2 border-primary border-opacity-25">
                <div class="timeline-dot position-absolute top-0 start-0 translate-middle rounded-circle bg-primary" style="width:12px;height:12px;margin-left:-1px;"></div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-dark">{{ $hist->title }}</strong>
                    <span class="badge bg-light text-muted border">{{ \Carbon\Carbon::parse($hist->created_at)->format('d M Y, h:i A') }}</span>
                </div>
                <p class="text-muted small mb-1">{{ $hist->description }}</p>
                <div class="small text-secondary">
                    <i class="fa-solid fa-user-check me-1"></i>Performed by: <strong>{{ $hist->performed_by ?? 'Admin' }}</strong> &bull; {{ \Carbon\Carbon::parse($hist->created_at)->diffForHumans() }}
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @else
          <div class="card-body text-center py-5 text-muted">
            <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 opacity-25"></i>
            <p>No activity history logged yet.</p>
          </div>
          @endif
        </div>
      </div>

      {{-- ──────── TAB 10: DOCUMENT MANAGEMENT (SECTION 12) ──────── --}}
      <div class="tab-pane fade" id="pane-documents" role="tabpanel">
        <div class="row g-4">
          {{-- Upload Form --}}
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header">
                <i class="fa-solid fa-cloud-arrow-up me-2 text-primary"></i>Upload Employee Document
              </div>
              <div class="card-body p-3">
                <form action="{{ route('employee.documents.store', $employee->id) }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Document Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Employment Contract 2026" required />
                  </div>
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select form-select-sm" required>
                      <option value="contract">Employment Contract</option>
                      <option value="national_id">National ID / Passport</option>
                      <option value="resume">Resume / CV</option>
                      <option value="academic">Academic Certificate</option>
                      <option value="medical">Medical / Health Record</option>
                      <option value="tax">Tax / Financial Document</option>
                      <option value="other">Other Document</option>
                    </select>
                  </div>
                  <div class="row g-2 mb-3">
                    <div class="col-6">
                      <label class="form-label small fw-bold">Issue Date</label>
                      <input type="date" name="issue_date" class="form-control form-control-sm" />
                    </div>
                    <div class="col-6">
                      <label class="form-label small fw-bold">Expiry Date</label>
                      <input type="date" name="expiry_date" class="form-control form-control-sm" />
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Select File <span class="text-danger">*</span></label>
                    <input type="file" name="document_file" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.zip" required />
                    <span class="text-muted" style="font-size: .72rem;">PDF, Word, Images, Zip (Max 10MB)</span>
                  </div>
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Notes / Description</label>
                    <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Optional notes..."></textarea>
                  </div>
                  <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-upload me-1"></i> Upload Document
                  </button>
                </form>
              </div>
            </div>
          </div>

          {{-- Document List --}}
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-header emp-section-header d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-folder-open me-2 text-info"></i>Archived Employee Documents</span>
                <span class="badge bg-light text-dark border">{{ $employee->documents->count() }} file(s)</span>
              </div>
              <div class="card-body p-0">
                @if($employee->documents->count() > 0)
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0" style="font-size: .875rem;">
                    <thead class="table-light">
                      <tr>
                        <th>Document</th>
                        <th>Category</th>
                        <th>Validity / Expiry</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th class="text-end">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($employee->documents as $doc)
                      <tr>
                        <td>
                          <div class="fw-semibold text-dark">{{ $doc->title }}</div>
                          @if($doc->notes)
                            <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $doc->notes }}</div>
                          @endif
                        </td>
                        <td>
                          <span class="badge bg-light text-dark border text-capitalize">{{ str_replace('_', ' ', $doc->category) }}</span>
                        </td>
                        <td>
                          @if($doc->expiry_date)
                            @if($doc->isExpired())
                              <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Expired ({{ $doc->expiry_date->format('M d, Y') }})</span>
                            @elseif($doc->isExpiringSoon(30))
                              <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Expiring Soon ({{ $doc->expiry_date->format('M d, Y') }})</span>
                            @else
                              <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-check me-1"></i>Valid till {{ $doc->expiry_date->format('M d, Y') }}</span>
                            @endif
                          @else
                            <span class="text-muted small">No Expiry</span>
                          @endif
                        </td>
                        <td>
                          <span class="text-muted small text-uppercase">{{ $doc->file_extension }} &bull; {{ $doc->formatted_file_size }}</span>
                        </td>
                        <td>
                          <div class="small text-muted">{{ $doc->created_at ? $doc->created_at->format('M d, Y') : '—' }}</div>
                          <div class="small text-secondary">{{ $doc->uploaded_by ?? 'Admin' }}</div>
                        </td>
                        <td class="text-end">
                          <div class="d-inline-flex gap-1">
                            <a href="{{ route('employee.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Download Document">
                              <i class="fa-solid fa-download"></i>
                            </a>
                            <form action="{{ route('employee.documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?');" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete Document">
                                <i class="fa-solid fa-trash-can"></i>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                  <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-25"></i>
                  <p class="mb-1 fw-semibold">No Documents Uploaded Yet</p>
                  <p class="small text-muted">Upload contracts, identification copies, or resumes on the left to keep records safe.</p>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>{{-- /tab-content --}}
  </div>{{-- /col-lg-9 col-md-8 --}}
</section>{{-- /row g-4 mb-4 --}}

{{-- ── PROFILE ADVANCE SALARY / OVERTIME PAYOUT MODAL ── --}}
<div class="modal fade" id="profileAdvanceModal" tabindex="-1" aria-labelledby="profileAdvanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title fw-bold" id="profileAdvanceModalLabel">
          <i class="fa-solid fa-hand-holding-dollar me-2"></i>{{ __('Pay in Advance / Overtime Payout') }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('payroll.advance-payment') }}" method="POST">
        @csrf
        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
        <div class="modal-body p-4">
          <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3 border">
            <div class="avatar-mono flex-shrink-0" style="width:40px;height:40px;background:#09090b;color:#fff;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
              {{ strtoupper(substr($employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($employee->lastname ?? 'M', 0, 1)) }}
            </div>
            <div>
              <div class="fw-bold fs-6">{{ $employee->firstname }} {{ $employee->lastname }}</div>
              <div class="text-muted small">{{ $employee->unique_id }} &bull; {{ optional($employee->department)->title ?? 'General' }}</div>
            </div>
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
            <input type="text" name="notes" class="form-control" placeholder="e.g. Mid-month advance, personal reason, or weekend OT">
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
/* ── Hero ── */
.emp-profile-hero { border-radius: 14px; overflow: hidden; border: 1px solid #e4e4e7; background: #fff; }
.emp-hero-banner {
  height: 90px;
  background: #09090b;
}
.emp-hero-body { padding: 0 20px 20px; margin-top: -30px; }
.emp-avatar-wrap { flex-shrink: 0; }
.emp-avatar {
  width: 78px; height: 78px; border-radius: 50%;
  background: #09090b;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.5rem; font-weight: 800;
  border: 4px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.emp-hero-name { font-size: 1.5rem; font-weight: 750; color: #09090b; }
.emp-status-badge { font-size: .75rem; }
.emp-hero-meta { font-size: .875rem; }

/* ── Stat pills ── */
.emp-stat-pills { margin-top: 8px; }
.emp-stat-pill {
  display: flex; flex-direction: column; align-items: center;
  background: #f4f4f5;
  border: 1px solid #e4e4e7;
  border-radius: 8px; padding: 8px 16px; min-width: 64px;
}
.emp-stat-num { font-size: 1.3rem; font-weight: 800; line-height: 1; color: #09090b; }
.emp-stat-label { font-size: .68rem; color: #71717a; text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }

/* ── Vertical Scrollable Tabs ── */
.emp-tab-sidebar {
  border-radius: 12px;
  border: 1px solid #e4e4e7;
  background: #fff;
  z-index: 10;
}
.emp-tab-scroll-wrap {
  overflow-y: auto;
  max-height: calc(100vh - 160px);
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}
.emp-tab-scroll-wrap::-webkit-scrollbar {
  width: 5px;
}
.emp-tab-scroll-wrap::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.emp-tab-nav-vertical {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.emp-tab-btn {
  background: transparent;
  border: 1px solid transparent;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: .88rem;
  font-weight: 500;
  color: #52525b;
  cursor: pointer;
  transition: all .15s ease;
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  text-align: left;
}
.emp-tab-btn:hover {
  background: #f4f4f5;
  color: #09090b;
}
.emp-tab-btn.active {
  background: #09090b !important;
  color: #ffffff !important;
  font-weight: 700;
  border-color: #09090b;
  box-shadow: 0 2px 8px rgba(0,0,0,.1);
}
.emp-tab-btn.active i {
  color: #9acd32 !important;
}
.emp-tab-btn.active .emp-tab-count {
  background: #9acd32 !important;
  color: #09090b !important;
}
.emp-tab-count {
  background: #f4f4f5;
  color: #27272a;
  font-size: .72rem;
  font-weight: 700;
  border-radius: 12px;
  padding: 2px 8px;
  line-height: 1.3;
}

/* ── Section headers ── */
.emp-section-header {
  background: #fafafa; border-bottom: 1px solid #e4e4e7;
  font-weight: 700; font-size: .88rem; padding: 12px 16px; color: #09090b;
}

/* ── Info grid ── */
.emp-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.emp-info-item { padding: 10px 4px; border-bottom: 1px solid #f4f4f5; display: flex; flex-direction: column; gap: 2px; }
.emp-info-full { grid-column: 1 / -1; }
.emp-info-label { font-size: .72rem; color: #71717a; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.emp-info-value { font-size: .9rem; color: #09090b; }

/* ── Salary ── */
.emp-salary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f4f4f5; font-size: .9rem; }
.emp-salary-total { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-radius: 8px; }
.emp-net-salary-box { background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 10px; padding: 16px; }

/* ── Payslip cards ── */
.emp-payslip-card { border-radius: 12px; transition: transform .2s, box-shadow .2s; border: 1px solid #e4e4e7; }
.emp-payslip-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.06) !important; }
.emp-payslip-header {
  display: flex; justify-content: space-between; align-items: center;
  background: #09090b;
  color: #fff; border-radius: 12px 12px 0 0 !important; padding: 14px 16px;
}
.emp-payslip-badge { background: #27272a; border-radius: 6px; padding: 4px 10px; font-size: .75rem; color: #f4f4f5; }
.emp-payslip-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f4f4f5; font-size: .875rem; }
.emp-payslip-net { display: flex; justify-content: space-between; align-items: center; background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 8px; padding: 10px 12px; margin-top: 8px; font-weight: 600; }
.emp-payslip-net-amount { color: #09090b; font-size: 1.15rem; font-weight: 800; }

.pulse-badge {
    box-shadow: 0 0 0 1px #ef4444;
}

/* ── Print ── */
@media print {
  #sidebar, .main > nav, .footer, .emp-hero-actions, .emp-stat-pills,
  .emp-tab-nav, .emp-tab-sidebar, .tab-pane:not(.active) { display: none !important; }
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
    .payslip{max-width:700px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e4e4e7;box-shadow:0 4px 16px rgba(0,0,0,.08)}
    .ps-header{background:#09090b;color:#fff;padding:28px 32px}
    .ps-header h2{font-size:1.5rem;margin-bottom:4px;letter-spacing:-0.02em}.ps-header p{color:#a1a1aa;font-size:.9rem}
    .ps-badge{background:#27272a;color:#f4f4f5;padding:4px 12px;border-radius:6px;font-size:.8rem;display:inline-block;margin-top:8px}
    .ps-body{padding:28px 32px}
    .ps-emp-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;background:#f4f4f5;border-radius:8px;padding:16px}
    .ps-emp-label{font-size:.72rem;color:#71717a;text-transform:uppercase;letter-spacing:.05em}
    .ps-emp-value{font-size:.9rem;font-weight:700;color:#09090b;margin-top:2px}
    .ps-section{margin-bottom:20px}
    .ps-section-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#71717a;border-bottom:1px solid #e4e4e7;padding-bottom:6px;margin-bottom:10px}
    .ps-row{display:flex;justify-content:space-between;padding:6px 0;font-size:.88rem;border-bottom:1px dashed #e4e4e7}
    .ps-total{display:flex;justify-content:space-between;padding:10px 14px;border-radius:6px;font-weight:700;margin-top:6px}
    .earn{background:#ecfdf5;color:#065f46}.ded{background:#fef2f2;color:#991b1b}
    .ps-net{background:#09090b;color:#fff;padding:18px 24px;border-radius:8px;margin-top:20px;display:flex;justify-content:space-between;align-items:center}
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
      <div class="ps-row"><span>Basic Salary</span><span>€ ${fmt(basic)}</span></div>
      <div class="ps-row"><span>House Rent Allowance</span><span>€ ${fmt(houseRent)}</span></div>
      <div class="ps-row"><span>Medical Allowance</span><span>€ ${fmt(medical)}</span></div>
      <div class="ps-total earn"><span>Gross Salary</span><span>€ ${fmt(gross)}</span></div>
    </div>
    <div class="ps-section">
      <div class="ps-section-title">Deductions</div>
      <div class="ps-row"><span>Total Deductions</span><span>− € ${fmt(deduction)}</span></div>
      <div class="ps-total ded"><span>Total Deducted</span><span>− € ${fmt(deduction)}</span></div>
    </div>
    <div class="ps-net">
      <div class="ps-net-label">Net Take-Home Salary</div>
      <div class="ps-net-amount">€ ${fmt(net)}</div>
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
