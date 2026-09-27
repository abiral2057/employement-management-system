@extends('layouts.admin')

@section('title')
    {{ __('Manage Late Time Attendance') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>{{ __('Late Arrival Tracking') }}
        </h1>
        <p class="text-muted fs-6 mb-0">{{ __('Audit late clock-ins against scheduled shift time-in.') }}</p>
    </div>
</div>
@endsection

@section('content')
{{-- ── Filters ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form action="{{ route('attendance.latetime') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">{{ __('Filter by Month') }}</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
            </div>
            <div class="col-md-6">
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

{{-- ── Late Time Table ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">
            <i class="fa-solid fa-list-ul me-2 text-warning"></i>{{ __('Late Arrival Entries') }}
        </h5>
        <span class="badge bg-light text-dark border">{{ $lateTimes->total() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase small text-muted">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>{{ __('Employee & Date') }}</th>
                    <th class="text-end" style="width: 220px;">{{ __('Late Duration & Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lateTimes as $index => $late)
                <tr>
                    <td class="text-muted fw-bold">{{ $lateTimes->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-mono flex-shrink-0" style="width:42px;height:42px;background:#09090b;color:#ffffff;font-size:.9rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                {{ strtoupper(substr($late->employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($late->employee->lastname ?? 'M', 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.employee.show', $late->employee->id ?? 1) }}" class="fw-bold fs-5 text-decoration-none text-dark d-block text-hover-primary">
                                    {{ $late->employee->firstname ?? '' }} {{ $late->employee->lastname ?? '' }}
                                </a>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border me-1">{{ $late->employee->unique_id ?? '—' }}</span>
                                    <span>{{ \Carbon\Carbon::parse($late->latetime_date)->format('d M Y (l)') }}</span>
                                    &bull; <span>Shift: {{ optional($late->employee->schedule)->title ?? 'Standard' }} ({{ optional($late->employee->schedule)->time_in ? date('g:i A', strtotime($late->employee->schedule->time_in)) : '—' }})</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1 fs-6 fw-bold">
                                <i class="fa-solid fa-clock me-1 text-danger"></i>{{ $late->latetime_duration }} late
                            </span>
                            <form action="{{ route('attendance.latetime.destroy', $late->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this late record?');">
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
                    <td colspan="3" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-circle-check fa-3x mb-3 text-success opacity-50"></i>
                        <p class="mb-0">No late arrival records found for this period. Perfect punctuality!</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lateTimes->hasPages())
    <div class="card-footer bg-transparent py-3">
        {{ $lateTimes->links() }}
    </div>
    @endif
</div>
@endsection