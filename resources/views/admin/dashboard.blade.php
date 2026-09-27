@extends('layouts.admin')

@section('title')
  {{ __('Dashboard') }}
@endsection

@section('header')
  @php
    $hour = now()->hour;
    $greeting = $hour < 12 ? __('Good Morning') : ($hour < 17 ? __('Good Afternoon') : __('Good Evening'));
    $greetIcon = $hour < 12 ? 'fa-sun' : ($hour < 17 ? 'fa-cloud-sun' : 'fa-moon');
  @endphp
  <div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3">
    <div>
      <h1 class="h1 mb-2">
        <i class="fas {{ $greetIcon }} text-warning me-2"></i>{{ $greeting }}, <strong>{{ Auth::user()->name }}</strong>
      </h1>
      <div class="text-muted fs-6">
        <i class="fas fa-calendar-alt me-1"></i><span id="liveDate">{{ now()->format('l, F j, Y') }}</span>
        <i class="fas fa-clock ms-4 me-1"></i><span id="liveTime">{{ now()->format('g:i:s A') }}</span>
      </div>
    </div>
    <div class="mt-3 mt-md-0">
      <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-1"></i>
        <span>{{ __('Print Dashboard') }}</span>
      </button>
    </div>
  </div>

  {{-- ── Quick Employee Search Bar ── --}}
  <div class="db-qsearch-wrap mb-4" id="dbSearchWrapper">
    <div class="db-qsearch-inner">
      <span class="db-qsearch-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
      <input
        type="text"
        id="dbEmpSearch"
        class="db-qsearch-input"
        placeholder="Quick-search employee by ID or name — e.g. EMP-001 or &quot;John&quot;…"
        autocomplete="off"
        spellcheck="false"
      />
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}" class="btn btn-sm btn-primary" title="Open full search page">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i><span class="d-none d-sm-inline">Full Search</span>
      </a>
    </div>
    <div id="dbSearchDropdown" class="db-qsearch-dropdown d-none"></div>
  </div>
@endsection

@section('content')
  @php
    $todayDayShifts = App\Models\ShiftRoster::whereDate('shift_date', today())->where('shift_type', 'day')->count();
    $todayNightShifts = App\Models\ShiftRoster::whereDate('shift_date', today())->where('shift_type', 'night')->count();
    $todayExtraShifts = App\Models\ShiftRoster::whereDate('shift_date', today())->where('shift_type', 'extra')->count();
    $totalOtPending = App\Models\OverTime::where('status', 'pending')->count();
    $pendingLeavesCount = App\Models\Leave::where('status', 0)->count();
    $unpaidPayrolls = App\Models\Payroll::where('payment_status', '!=', 'paid')->whereNotNull('due_date')->where('due_date', '<', today()->toDateString())->count();
    $totalDueBalance = App\Models\Payroll::where('payment_status', '!=', 'paid')->get()->sum(fn($p) => $p->balance_due);
    $activeEmployeesCount = App\Models\Employee::where('status', 1)->count();
    $presentTodayCount = App\Models\Attendance::whereDate('attendance_date', today())->where('status', 1)->count();
    $absentTodayCount = App\Models\Attendance::whereDate('attendance_date', today())->where('status', 0)->count();
  @endphp

  {{-- ── Quick Action Hub (Section 6 & 19 of Checklist) ── --}}
  <div class="card border-0 shadow-sm mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body p-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary px-2 py-1"><i class="fa-solid fa-bolt me-1"></i>Quick Actions</span>
          <span class="text-muted small d-none d-md-inline">Execute primary HR workflows in 1 click:</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.create') : route('hr.employee.create') ) }}" class="btn btn-sm btn-outline-primary fw-semibold">
            <i class="fa-solid fa-user-plus me-1"></i> Add Employee
          </a>
          <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('attendance.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.attendance.index') : route('moderator.attendance.index') ) }}" class="btn btn-sm btn-outline-secondary fw-semibold">
            <i class="fa-solid fa-calendar-check me-1"></i> Daily Attendance
          </a>
          <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('leaves.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.leaves.index') : route('hr.leaves.index') ) }}" class="btn btn-sm btn-outline-warning text-dark fw-semibold position-relative">
            <i class="fa-solid fa-person-walking-arrow-right me-1"></i> Leaves
            @if($pendingLeavesCount > 0)
              <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $pendingLeavesCount }}</span>
            @endif
          </a>
          <a href="{{ route('payroll.create') }}" class="btn btn-sm btn-outline-success fw-semibold">
            <i class="fa-solid fa-calculator me-1"></i> Generate Payroll
          </a>
          <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('super.performance.report') : route('admin.performance.report') }}" class="btn btn-sm btn-outline-dark fw-semibold">
            <i class="fa-solid fa-chart-pie me-1"></i> Reports
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Action Center: What Requires Immediate Attention (5-Second Rule) ── --}}
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small text-uppercase fw-bold text-muted">Pending Leaves</span>
            <span class="badge bg-warning bg-opacity-10 text-warning">{{ $pendingLeavesCount }} queue</span>
          </div>
          <div class="fs-4 fw-bold text-dark mb-1">{{ $pendingLeavesCount }}</div>
          <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('leaves.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.leaves.index') : route('hr.leaves.index') ) }}" class="small text-warning fw-semibold text-decoration-underline">Review & Approve &rarr;</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-primary border-4">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small text-uppercase fw-bold text-muted">OT Pending Claims</span>
            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $totalOtPending }} claims</span>
          </div>
          <div class="fs-4 fw-bold text-dark mb-1">{{ $totalOtPending }}</div>
          <a href="{{ route('attendance.overtime') }}" class="small text-primary fw-semibold text-decoration-underline">Approve Claims &rarr;</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small text-uppercase fw-bold text-muted">Payment Overdue</span>
            <span class="badge bg-danger bg-opacity-10 text-danger">{{ $unpaidPayrolls }} pay</span>
          </div>
          <div class="fs-4 fw-bold text-danger mb-1">€ {{ number_format($totalDueBalance, 2) }}</div>
          <a href="{{ route('payroll.due') }}" class="small text-danger fw-semibold text-decoration-underline">Send Reminders &rarr;</a>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
        <div class="card-body p-3">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small text-uppercase fw-bold text-muted">Present Today</span>
            <span class="badge bg-success bg-opacity-10 text-success">{{ $activeEmployeesCount }} active</span>
          </div>
          <div class="fs-4 fw-bold text-success mb-1">{{ $presentTodayCount }} <span class="fs-6 text-muted fw-normal">/ {{ $activeEmployeesCount }}</span></div>
          <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('attendance.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.attendance.index') : route('moderator.attendance.index') ) }}" class="small text-success fw-semibold text-decoration-underline">Daily Log &rarr;</a>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Quick Feature Tracker Banner ── --}}
  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card border shadow-sm h-100 bg-white highlight-module">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-uppercase small fw-bold text-dark">
              <i class="fa-solid fa-business-time me-2 text-dark"></i>{{ __('Shift Coverage Today') }}
            </span>
            <a href="{{ route('shifts.roster') }}" class="btn btn-sm btn-primary py-1 px-3">Manage</a>
          </div>
          <div class="d-flex gap-2 align-items-center mt-3">
            <span class="badge bg-light text-dark border px-2 py-1"><i class="fa-solid fa-sun me-1"></i>{{ $todayDayShifts }} Day</span>
            <span class="badge bg-dark text-white px-2 py-1"><i class="fa-solid fa-moon me-1"></i>{{ $todayNightShifts }} Night</span>
            <span class="badge badge-yellowgreen px-2 py-1"><i class="fa-solid fa-bolt me-1"></i>{{ $todayExtraShifts }} Extra</span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border shadow-sm h-100 bg-white highlight-module">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-uppercase small fw-bold text-dark">
              <i class="fa-solid fa-stopwatch me-2 text-dark"></i>{{ __('Overtime (OT) Claims') }}
            </span>
            <a href="{{ route('attendance.overtime') }}" class="btn btn-sm btn-primary py-1 px-3">Review</a>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <span class="fw-bold fs-4 text-dark">{{ $totalOtPending }}</span>
              <span class="small text-muted ms-1">pending claims</span>
            </div>
            <a href="{{ route('attendance.overtime') }}" class="small fw-bold text-dark text-decoration-underline">Approve OT &rarr;</a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border shadow-sm h-100 bg-white highlight-module {{ $unpaidPayrolls > 0 ? 'border-danger' : '' }}">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-uppercase small fw-bold {{ $unpaidPayrolls > 0 ? 'text-danger' : 'text-dark' }}">
              <i class="fa-solid fa-file-invoice-dollar me-2"></i>{{ __('Payment Due & Reminders') }}
            </span>
            <a href="{{ route('payroll.due') }}" class="btn btn-sm btn-primary py-1 px-3">Due Center</a>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              <span class="fw-bold fs-4 {{ $unpaidPayrolls > 0 ? 'text-danger' : 'text-dark' }}">€ {{ number_format($totalDueBalance, 2) }}</span>
              <span class="small text-muted ms-1">({{ $unpaidPayrolls }} overdue)</span>
            </div>
            <a href="{{ route('payroll.due') }}" class="small fw-bold {{ $unpaidPayrolls > 0 ? 'text-danger' : 'text-dark' }} text-decoration-underline">Send Reminder &rarr;</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="row">
    <div class="col-sm-6">
      <div class="row">
        <div class="col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col mt-0">
                  <a href="{{ route('schedule.index') }}">
                    <h5 class="card-title">{{ __('Schedules') }}</h5>
                  </a>
                </div>
                <div class="col-auto">
                  <div class="stat text-primary">
                    <i class="fa-solid fa-clock"></i>
                  </div>
                </div>
              </div>
              <h1 class="mt-3 mb-3">
                {{ App\Models\Schedule::count(); }}
              </h1>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col mt-0">
                  <a href="{{ route('department.index') }}">
                    <h5 class="card-title">{{ __('Departments') }}</h5>
                  </a>
                </div>
                <div class="col-auto">
                  <div class="stat text-primary">
                    <i class="fa-solid fa-users-gear"></i>
                  </div>
                </div>
              </div>
              <h1 class="mt-3 mb-3">
                {{ App\Models\Department::where('status', 1)->count(); }}
              </h1>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col mt-0">
                  <a href="{{ route('employee.index') }}">
                    <h5 class="card-title">{{ __('Employees') }}</h5>
                  </a>
                </div>
                <div class="col-auto">
                  <div class="stat text-primary">
                    <i class="fa-solid fa-users-viewfinder"></i>
                  </div>
                </div>
              </div>
              <h1 class="mt-3 mb-3">
                {{ App\Models\Employee::where('status', 1)->count(); }}
              </h1>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col mt-0">
                  <a href="{{ route('user.index') }}">
                    <h5 class="card-title">{{ __('Users & Members') }}</h5>
                  </a>
                </div>
                <div class="col-auto">
                  <div class="stat text-primary">
                    <i class="fas fa-user align-middle"></i>
                  </div>
                </div>
              </div>
              <h1 class="mt-3 mb-3">
                {{ App\Models\User::where('status', 1)->count(); }}
              </h1>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="card flex-fill">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between my- py-0">
            <h5 class="card-title mb-0">{{ __('Users & Members') }}</h5>
            <a href="{{ route('user.index') }}" class="btn btn-secondary btn-sm my-0">
              <i class="fas fa-eye"></i>
              <span class="ps-1">{{ __('View') }}</span>
            </a>
          </div>
        </div>
        <div class="card-body pt-0 mt-0">
          <table class="table table-hover my-0">
            <thead>
              <tr>
                <th>{{ __('SL') }}</th>
                <th>{{ __('Name of User') }}</th>
                <th>{{ __('User Role') }}</th>
                <th>{{ __('Date Joined') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($users as $user)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{ route('user.edit', $user->id) }}">
                      <strong>{{ $user->name }}</strong>
                    </a>
                  </td>
                  <td><span class="badge bg-info">{{ $user->role->title }}</span></td>
                  <td>{{ $user->created_at->diffforhumans() }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
  <section class="row">
    <div class="col-sm-12">
      <div class="card flex-fill w-100">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between my- py-0">
            <h5 class="card-title mb-0">{{ __('Employee DataTable') }}</h5>
            <a href="{{ route('employee.index') }}" class="btn btn-secondary btn-sm my-0">
              <i class="fas fa-eye"></i>
              <span class="ps-1">{{ __('View') }}</span>
            </a>
          </div>
        </div>
        <div class="card-body pt-0 mt-0">
          <table class="table table-hover align-middle my-0">
            <thead class="table-light text-uppercase small text-muted">
              <tr>
                <th style="width: 50px;">#</th>
                <th>{{ __('Employee Name') }}</th>
                <th class="text-end" style="width: 140px;">{{ __('Action') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($employees as $employee)
                <tr>
                  <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar-mono flex-shrink-0" style="width:38px;height:38px;background:#09090b;color:#ffffff;font-size:.85rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        {{ strtoupper(substr($employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($employee->lastname ?? 'M', 0, 1)) }}
                      </div>
                      <div>
                        <a href="{{ route('admin.employee.show', $employee->id ?? 1) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                          {{ $employee->firstname }} {{ $employee->lastname }}
                        </a>
                        <div class="small text-muted">
                          <span class="badge bg-light text-dark border me-1">{{ $employee->unique_id ?? ('#' . $employee->id) }}</span>
                          <span>{{ optional($employee->department)->title ?? 'General' }}</span> &bull; <span>{{ optional($employee->designation)->title ?? 'Staff' }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">
                    <a href="{{ route('admin.employee.show', $employee->id ?? 1) }}" class="btn btn-dark btn-sm fw-semibold px-3">
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
  <section class="row" id="printArea">
    <div class="col-12">
      <div class="card flex-fill w-100">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between my- py-0">
            <h5 class="card-title mb-0">{{ __('Chart Analysis') }}</h5>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="printCharts()">
              <i class="fas fa-print"></i>
              <span class="ps-1">{{ __('Print Charts') }}</span>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="text-muted mb-3">{{ __('Employees per Department') }}</h6>
              <div class="chart chart-sm">
                <canvas id="departmentChart"></canvas>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-3">{{ __('Attendance Trend (Last 7 Days)') }}</h6>
              <div class="chart chart-sm">
                <canvas id="attendanceChart"></canvas>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-3">{{ __('Employee Gender') }}</h6>
              <div class="chart chart-sm">
                <canvas id="genderChart"></canvas>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="text-muted mb-3">{{ __('Net Salary Distribution') }}</h6>
              <div class="chart chart-sm">
                <canvas id="payrollChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@section('script')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script id="chartScript">
    const chartColors = ['#9acd32', '#09090b', '#71717a', '#84cc16', '#27272a', '#a3e635', '#52525b', '#4d7c0f'];
    const chartData = {
      departments: { labels: @json($departmentChart['labels']), counts: @json($departmentChart['counts']) },
      attendance: { labels: @json($attendanceTrend->pluck('label')), present: @json($attendanceTrend->pluck('present')) },
      gender: { labels: @json($genderChart['labels']), counts: @json($genderChart['counts']) },
      payroll: { labels: @json($payrollTrend->pluck('label')), totals: @json($payrollTrend->pluck('total')) }
    };

    function renderCharts() {
      new Chart(document.getElementById('departmentChart'), {
        type: 'bar',
        data: {
          labels: chartData.departments.labels,
          datasets: [{
            label: 'Employees',
            data: chartData.departments.counts,
            backgroundColor: chartColors,
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
      });

      new Chart(document.getElementById('attendanceChart'), {
        type: 'line',
        data: {
          labels: chartData.attendance.labels,
          datasets: [{
            label: 'Present',
            data: chartData.attendance.present,
            borderColor: '#9acd32',
            backgroundColor: 'rgba(154, 205, 50, 0.22)',
            fill: true,
            tension: 0.35,
            pointRadius: 5,
            pointBackgroundColor: '#9acd32',
            pointBorderColor: '#09090b'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
      });

      new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
          labels: chartData.gender.labels,
          datasets: [{
            data: chartData.gender.counts,
            backgroundColor: ['#9acd32', '#09090b'],
            borderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } }
        }
      });

      new Chart(document.getElementById('payrollChart'), {
        type: 'bar',
        data: {
          labels: chartData.payroll.labels,
          datasets: [{
            label: 'Net Salary',
            data: chartData.payroll.totals,
            backgroundColor: '#9acd32',
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    function updateClock() {
      const now = new Date();
      const dateEl = document.getElementById('liveDate');
      const timeEl = document.getElementById('liveTime');
      if (dateEl) {
        dateEl.textContent = now.toLocaleDateString(undefined, {
          weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
      }
      if (timeEl) {
        timeEl.textContent = now.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      }
    }

    function printCharts() {
      const content = document.getElementById('printArea').cloneNode(true);
      const scriptSource = document.getElementById('chartScript').textContent;
      const printWindow = window.open('', '_blank', 'width=1200,height=800');
      printWindow.document.write(`
        <html>
          <head>
            <title>Chart Analysis</title>
            <style>
              body { padding: 24px; font-family: 'Segoe UI', sans-serif; }
              h2 { margin: 0 0 16px; }
              .card { border: 1px solid #dee2e6; border-radius: 10px; padding: 16px; }
              .card-body { padding-top: 16px; }
              .card-header h5 { margin: 0; font-size: 1rem; }
              .chart-sm { height: 280px; position: relative; }
              .row { display: flex; flex-wrap: wrap; margin: 0 -8px; }
              .col-md-6 { flex: 0 0 50%; max-width: 50%; box-sizing: border-box; padding: 8px 12px; }
              #printArea .btn, .card-header .btn { display: none; }
              .text-muted { color: #6c757d; }
              .mb-3 { margin-bottom: 1rem; }
            </style>
          </head>
          <body>
            <h2>Chart Analysis</h2>
            <div id="printArea">${content.innerHTML}</div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"><\/script>
            <script>${scriptSource}<\/script>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }

    renderCharts();
    setInterval(updateClock, 1000);
  </script>

  {{-- ── Dashboard Quick Search: styles + AJAX ── --}}
  <style>
  .db-qsearch-wrap {
    position: relative;
    background: #ffffff;
    border: 1.5px solid #e4e4e7;
    border-radius: 12px;
    padding: 10px 14px;
    transition: box-shadow .2s;
  }
  .db-qsearch-wrap:focus-within {
    box-shadow: 0 0 0 2px rgba(9,9,11,.1);
    border-color: #09090b;
  }
  .db-qsearch-inner {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
  }
  .db-qsearch-icon {
    color: #09090b;
    font-size: 1.05rem;
    flex-shrink: 0;
  }
  .db-qsearch-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: .97rem;
    color: inherit;
    min-width: 0;
    padding: 4px 0;
  }
  .db-qsearch-input::placeholder { color: #a1a1aa; }
  .db-qsearch-link {
    flex-shrink: 0;
    font-size: .8rem;
    font-weight: 600;
    color: #09090b;
    text-decoration: none;
    background: #f4f4f5;
    border: 1px solid #e4e4e7;
    border-radius: 8px;
    padding: 4px 10px;
    white-space: nowrap;
    transition: background .15s;
  }
  .db-qsearch-link:hover { background: #e4e4e7; color: #000; }
  /* Dropdown */
  .db-qsearch-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0; right: 0;
    background: #fff;
    border: 1px solid #e4e4e7;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    z-index: 1050;
    max-height: 340px;
    overflow-y: auto;
    animation: dbDropIn .15s ease;
  }
  @keyframes dbDropIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
  .db-ac-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    cursor: pointer;
    transition: background .12s;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f4f4f5;
  }
  .db-ac-item:last-child { border-bottom: none; }
  .db-ac-item:hover { background: #fafafa; }
  .db-ac-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: #09090b;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .78rem; font-weight: 700; flex-shrink: 0;
  }
  .db-ac-info { flex: 1; min-width: 0; }
  .db-ac-name { font-weight: 600; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .db-ac-sub  { font-size: .76rem; color: #6c757d; }
  .db-ac-id   { font-size: .75rem; font-weight: 700; color: #3b7ddd; background: rgba(59,125,221,.1); padding: 2px 8px; border-radius: 20px; white-space: nowrap; }
  .db-ac-status-active   { display:inline-block; width:7px; height:7px; border-radius:50%; background:#1cbb8c; margin-right:4px; }
  .db-ac-status-inactive { display:inline-block; width:7px; height:7px; border-radius:50%; background:#adb5bd; margin-right:4px; }
  .db-ac-empty { padding: 16px; text-align: center; color: #adb5bd; font-size: .88rem; }
  </style>

  <script>
  (function() {
    const input    = document.getElementById('dbEmpSearch');
    const dropdown = document.getElementById('dbSearchDropdown');
    const wrapper  = document.getElementById('dbSearchWrapper');
    if (!input || !dropdown) return;

    const ajaxUrl   = "{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search.ajax') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search.ajax') : route('hr.employee.search.ajax')) }}";
    const profileUrl = "{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}";

    function initials(n) { return n.split(' ').map(w=>w[0]||'').join('').toUpperCase().slice(0,2); }

    let timer;
    input.addEventListener('input', function() {
      clearTimeout(timer);
      const q = this.value.trim();
      if (q.length < 1) { dropdown.classList.add('d-none'); dropdown.innerHTML=''; return; }
      timer = setTimeout(() => {
        fetch(`${ajaxUrl}?query=${encodeURIComponent(q)}`)
          .then(r => r.json())
          .then(data => {
            dropdown.innerHTML = '';
            if (!data.length) {
              dropdown.innerHTML = '<div class="db-ac-empty"><i class="fas fa-search me-1"></i>No employee found for "'+q+'"</div>';
            } else {
              data.forEach(emp => {
                const a = document.createElement('a');
                a.className = 'db-ac-item';
                a.href = `${profileUrl}?query=${encodeURIComponent(emp.unique_id)}`;
                const dot = emp.status == 1
                  ? '<span class="db-ac-status-active"></span>Active'
                  : '<span class="db-ac-status-inactive"></span>Inactive';
                a.innerHTML = `
                  <div class="db-ac-avatar">${initials(emp.name)}</div>
                  <div class="db-ac-info">
                    <div class="db-ac-name">${emp.name}</div>
                    <div class="db-ac-sub">${emp.department} &bull; ${emp.designation} &bull; ${dot}</div>
                  </div>
                  <div class="db-ac-id">${emp.unique_id}</div>`;
                dropdown.appendChild(a);
              });
            }
            dropdown.classList.remove('d-none');
          })
          .catch(() => { dropdown.classList.add('d-none'); });
      }, 220);
    });

    // Enter key → go to full search
    input.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const q = this.value.trim();
        if (q) window.location.href = `${profileUrl}?query=${encodeURIComponent(q)}`;
      }
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
      if (!wrapper.contains(e.target)) dropdown.classList.add('d-none');
    });
  })();
  </script>
@endsection