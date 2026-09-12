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
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <div>
      <h1 class="h3 mb-1">
        <i class="fas {{ $greetIcon }} text-warning me-2"></i>{{ $greeting }}, <strong>{{ Auth::user()->name }}</strong>
      </h1>
      <div class="text-muted">
        <i class="fas fa-calendar-alt me-1"></i><span id="liveDate">{{ now()->format('l, F j, Y') }}</span>
        <i class="fas fa-clock ms-3 me-1"></i><span id="liveTime">{{ now()->format('g:i:s A') }}</span>
      </div>
    </div>
    <div class="mt-2 mt-md-0">
      <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
        <i class="fas fa-print"></i>
        <span class="ps-1">{{ __('Print Dashboard') }}</span>
      </button>
    </div>
  </div>

  {{-- ── Quick Employee Search Bar ── --}}
  <div class="db-qsearch-wrap mb-1" id="dbSearchWrapper">
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
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}" class="db-qsearch-link" title="Open full search page">
        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i><span class="d-none d-sm-inline">Full Search</span>
      </a>
    </div>
    <div id="dbSearchDropdown" class="db-qsearch-dropdown d-none"></div>
  </div>
@endsection

@section('content')
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
          <table class="table table-hover my-0">
            <thead>
              <tr>
                <th>{{ __('SL') }}</th>
                <th>{{ __('Name of Employee') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('Designation') }}</th>
                <th>{{ __('Appointed') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($employees as $employee)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{ route('employee.edit', $employee->id) }}">
                      <strong>{{ $employee->firstname . ' ' . $employee->lastname }}</strong>
                    </a>
                  </td>
                  <td>{{ $employee->department->title }}</td>
                  <td>{{ $employee->designation->title }}</td>
                  <td>{{ $employee->created_at->diffforhumans() }}</td>
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
    const chartColors = ['#3b7ddd', '#1cbb8c', '#fcb92c', '#e83e8c', '#6f42c1', '#17a2b8', '#dc3545', '#fd7e14'];
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
            borderColor: '#3b7ddd',
            backgroundColor: 'rgba(59, 125, 221, 0.15)',
            fill: true,
            tension: 0.35,
            pointRadius: 5
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
            backgroundColor: ['#3b7ddd', '#e83e8c'],
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
            backgroundColor: '#1cbb8c',
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
    background: linear-gradient(135deg,rgba(59,125,221,.07),rgba(28,187,140,.07));
    border: 1.5px solid rgba(59,125,221,.18);
    border-radius: 14px;
    padding: 10px 14px;
    transition: box-shadow .2s;
  }
  .db-qsearch-wrap:focus-within {
    box-shadow: 0 0 0 3px rgba(59,125,221,.18);
    border-color: #3b7ddd;
  }
  .db-qsearch-inner {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
  }
  .db-qsearch-icon {
    color: #3b7ddd;
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
  .db-qsearch-input::placeholder { color: #adb5bd; }
  .db-qsearch-link {
    flex-shrink: 0;
    font-size: .8rem;
    font-weight: 600;
    color: #3b7ddd;
    text-decoration: none;
    background: rgba(59,125,221,.1);
    border-radius: 8px;
    padding: 4px 10px;
    white-space: nowrap;
    transition: background .15s;
  }
  .db-qsearch-link:hover { background: rgba(59,125,221,.2); color: #2563eb; }
  /* Dropdown */
  .db-qsearch-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0; right: 0;
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 14px;
    box-shadow: 0 12px 40px rgba(0,0,0,.13);
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
    border-bottom: 1px solid #f5f7fa;
  }
  .db-ac-item:last-child { border-bottom: none; }
  .db-ac-item:hover { background: #f0f4ff; }
  .db-ac-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg,#3b7ddd,#1cbb8c);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .78rem; font-weight: 700; flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(59,125,221,.3);
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