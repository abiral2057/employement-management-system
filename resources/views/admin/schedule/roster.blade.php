@extends('layouts.admin')

@section('title')
    {{ __('Shift Roster & Day/Night/Extra Shift Management') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-business-time text-dark me-2"></i>{{ __('Shift Roster & Shift Tracking') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Manage, schedule, and track Day Shifts, Night Shifts (cross-midnight), and Extra Shifts with overtime & extra allowances.
        </p>
    </div>
    <div class="d-flex gap-3 flex-wrap">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#quickExtraModal">
            <i class="fa-solid fa-star me-1"></i> {{ __('Quick Extra Shift') }}
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
            <i class="fa-solid fa-plus me-1"></i> {{ __('Assign Shift') }}
        </button>
        <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-sliders me-1"></i> {{ __('Manage Shift Templates') }}
        </a>
    </div>
</div>
@endsection

@section('content')
{{-- ── KPI Metric Cards ── --}}
<div class="row g-4 mb-5">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Total Scheduled') }}</span>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold">{{ number_format($stats['total_shifts']) }}</h2>
                <div class="text-muted small">{{ __('This Month Roster Entries') }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Day Shifts') }}</span>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-sun"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-info">{{ number_format($stats['day_shifts']) }}</h2>
                <div class="small text-muted"><span class="badge bg-info bg-opacity-10 text-info">{{ $stats['today_day'] }} today</span> active coverage</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Night Shifts') }}</span>
                    <div class="stat-icon bg-indigo bg-opacity-10 text-purple" style="background:#f3e8ff;color:#9333ea;">
                        <i class="fa-solid fa-moon"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold" style="color:#9333ea;">{{ number_format($stats['night_shifts']) }}</h2>
                <div class="small text-muted"><span class="badge" style="background:#f3e8ff;color:#9333ea;">{{ $stats['today_night'] }} today</span> cross-midnight duties</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">{{ __('Extra Shifts') }}</span>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                </div>
                <h2 class="h3 mb-1 fw-bold text-warning">{{ number_format($stats['extra_shifts']) }}</h2>
                <div class="small text-muted"><span class="badge bg-warning bg-opacity-20 text-dark">{{ $stats['today_extra'] }} today</span> with extra allowance</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter Bar ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form action="{{ route('shifts.roster') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Filter by Month') }}</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">{{ __('Filter by Specific Date') }}</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">{{ __('Shift Type') }}</label>
                <select name="shift_type" class="form-select form-select-sm">
                    <option value="all" {{ $shiftTypeFilter === 'all' ? 'selected' : '' }}>All Shift Types</option>
                    <option value="day" {{ $shiftTypeFilter === 'day' ? 'selected' : '' }}>Day Shift</option>
                    <option value="night" {{ $shiftTypeFilter === 'night' ? 'selected' : '' }}>Night Shift</option>
                    <option value="extra" {{ $shiftTypeFilter === 'extra' ? 'selected' : '' }}>Extra Shift</option>
                </select>
            </div>
            <div class="col-md-3">
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
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply Filters">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Shift Roster Table ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
        <h5 class="card-title mb-0 fw-bold">
            <i class="fa-solid fa-list-check me-2 text-primary"></i>{{ __('Scheduled Roster Details') }}
        </h5>
        <span class="badge bg-light text-dark border">{{ $rosters->total() }} records found</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase small text-muted">
                <tr>
                    <th scope="col" style="width: 50px;">#</th>
                    <th scope="col">{{ __('Employee & Date') }}</th>
                    <th scope="col">{{ __('Shift Schedule & Status') }}</th>
                    <th scope="col" class="text-end" style="width: 120px;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rosters as $index => $roster)
                <tr>
                    <td class="text-muted fw-bold">{{ $rosters->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-mono flex-shrink-0" style="width:42px;height:42px;background:#09090b;color:#ffffff;font-size:.9rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                {{ strtoupper(substr($roster->employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($roster->employee->lastname ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.employee.show', $roster->employee->id ?? 1) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                                    {{ $roster->employee->firstname ?? '' }} {{ $roster->employee->lastname ?? '' }}
                                </a>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border me-1">{{ $roster->employee->unique_id ?? '—' }}</span>
                                    <span>{{ \Carbon\Carbon::parse($roster->shift_date)->format('d M Y (l)') }}</span>
                                    &bull; <span>{{ optional($roster->employee->department)->title ?? 'General' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            @if($roster->shift_type === 'day')
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-sun me-1"></i> Day Shift
                                </span>
                            @elseif($roster->shift_type === 'night')
                                <span class="badge px-2 py-1" style="background:#f3e8ff;color:#7e22ce;border:1px solid #d8b4fe;">
                                    <i class="fa-solid fa-moon me-1"></i> Night Shift
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-50 px-2 py-1">
                                    <i class="fa-solid fa-bolt me-1"></i> Extra Shift
                                </span>
                            @endif
                            <span class="fw-semibold text-dark">{{ optional($roster->schedule)->title ?? 'Standard Shift' }} ({{ $roster->hours }} hrs)</span>
                            @if($roster->extra_allowance > 0)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">+€ {{ number_format($roster->extra_allowance, 2) }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <form action="{{ route('shifts.status.update', $roster->id) }}" method="POST" class="d-inline">
                                @csrf
                                <select name="status" class="form-select form-select-sm border bg-light fw-semibold" onchange="this.form.submit()" style="width: 140px; font-size: .8rem;">
                                    <option value="scheduled" {{ $roster->status === 'scheduled' ? 'selected' : '' }}>⏳ Scheduled</option>
                                    <option value="completed" {{ $roster->status === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                    <option value="swapped" {{ $roster->status === 'swapped' ? 'selected' : '' }}>🔄 Swapped</option>
                                    <option value="absent" {{ $roster->status === 'absent' ? 'selected' : '' }}>❌ Absent</option>
                                </select>
                            </form>
                            @if($roster->notes)
                                <span class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $roster->notes }}">
                                    <i class="fa-regular fa-comment me-1"></i>{{ $roster->notes }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end">
                        <form action="{{ route('shifts.destroy', $roster->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm px-2" onclick="return confirm('Remove this shift roster assignment?')" title="Delete assignment">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-calendar-xmark fa-3x mb-3 opacity-25"></i>
                        <p class="mb-2">No shift roster records found matching your filters.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
                            <i class="fa-solid fa-plus me-1"></i> Assign First Shift
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rosters->hasPages())
    <div class="card-footer bg-transparent py-3">
        {{ $rosters->links() }}
    </div>
    @endif
</div>

{{-- ── MODAL: ASSIGN SHIFT (Day / Night / Extra) ── --}}
<div class="modal fade" id="assignShiftModal" tabindex="-1" aria-labelledby="assignShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="assignShiftModalLabel">
                    <i class="fa-solid fa-calendar-plus me-2"></i>{{ __('Assign Employee Shift') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('shifts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Select Employee') }} <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->unique_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Shift Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="shift_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Shift Type') }} <span class="text-danger">*</span></label>
                            <select name="shift_type" class="form-select" id="shiftTypeSelect" required>
                                <option value="day">☀️ Day Shift</option>
                                <option value="night">🌙 Night Shift</option>
                                <option value="extra">⚡ Extra Shift</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Shift Template (Schedule)') }} <span class="text-danger">*</span></label>
                        <select name="schedule_id" class="form-select" required id="scheduleSelect">
                            @foreach($schedules as $sch)
                                <option value="{{ $sch->id }}" data-hours="{{ $sch->duration_hours }}">
                                    {{ $sch->title }} ({{ date('g:i A', strtotime($sch->time_in)) }} - {{ date('g:i A', strtotime($sch->time_out)) }})
                                    @if($sch->isNightShift()) [Night / +1d] @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Planned Hours') }}</label>
                            <input type="number" step="0.5" name="hours" id="shiftHoursInput" class="form-control" value="8" min="1" max="24">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Extra Allowance (€)') }}</label>
                            <input type="number" step="0.01" name="extra_allowance" class="form-control" value="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Notes / Instructions') }}</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Special coverage, Night shift supervision, Weekend extra duty"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> Save Shift Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── MODAL: QUICK EXTRA SHIFT ── --}}
<div class="modal fade" id="quickExtraModal" tabindex="-1" aria-labelledby="quickExtraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="quickExtraModalLabel">
                    <i class="fa-solid fa-bolt me-2"></i>{{ __('Quick Extra Shift Assignment') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('shifts.extra.quick') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">
                        Assign an urgent or extra duty shift to an employee on a specific date. Extra shift allowances will be recorded in the employee's history and payroll calculation.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Employee') }} <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Choose Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->firstname }} {{ $emp->lastname }} ({{ $emp->unique_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Shift Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="shift_date" class="form-control" value="{{ today()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Shift Template') }} <span class="text-danger">*</span></label>
                            <select name="schedule_id" class="form-select" required>
                                @foreach($schedules as $sch)
                                    <option value="{{ $sch->id }}">
                                        {{ $sch->title }} ({{ $sch->duration_hours }} hrs)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Extra Duty Allowance (€)') }}</label>
                        <input type="number" step="0.01" name="extra_allowance" class="form-control" placeholder="e.g. 500.00" value="500.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Reason for Extra Shift') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Emergency machine maintenance, holiday rush coverage">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-semibold">
                        <i class="fa-solid fa-check me-1"></i> Assign Extra Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<style>
.kpi-card { border-radius: 12px; transition: transform .15s ease, box-shadow .15s ease; }
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.08) !important; }
.stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
</style>
<script>
document.getElementById('scheduleSelect')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const hrs = selected.getAttribute('data-hours');
    if (hrs) {
        document.getElementById('shiftHoursInput').value = hrs;
    }
});
</script>
@endsection
