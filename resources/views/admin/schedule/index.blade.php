@extends('layouts.admin')

@section('title')
    {{ __('Manage Shift Schedules') }}
@endsection

@section('header')
<div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
    <div>
        <h1 class="h3 mb-1">{{ __('Shift Schedules & Templates') }}</h1>
        <p class="text-muted small mb-0">{{ __('Define master shift timings for Day, Night, and Extra shifts.') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('shifts.roster') }}" class="btn btn-warning btn-sm text-dark fw-semibold">
            <i class="fa-solid fa-business-time me-1"></i> {{ __('View Daily Shift Roster') }}
        </a>
        <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('schedule.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.schedule.create') : route('moderator.schedule.create') ) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus me-1"></i> {{ __('Add New Schedule') }}
        </a>
    </div>
</div>
@endsection

@section('content')
<section class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm flex-fill">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">              
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fa-solid fa-clock text-primary me-2"></i>{{ __('Active Schedules') }}
                </h5>
                <span class="badge bg-light text-dark border">{{ $schedules->count() }} Schedules</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle my-0 data-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th scope="col" style="width: 70px;">#</th>
                            <th scope="col">{{ __('Shift Schedule') }}</th>
                            <th scope="col" class="text-end" style="width: 220px;">{{ __('Assigned Staff & Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                        <tr>
                            <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                            <td class="py-3">
                                <div class="fw-bold fs-4 text-dark mb-1" style="letter-spacing: -0.025em; line-height: 1.2;">
                                    {{ $schedule->title }}
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-light text-dark border py-1 px-2 fw-semibold">
                                        <i class="fa-regular fa-clock me-1 text-secondary"></i>
                                        {{ date('g:i A', strtotime($schedule->time_in)) }} &ndash; {{ date('g:i A', strtotime($schedule->time_out)) }}
                                        ({{ $schedule->duration_hours }} hrs)
                                    </span>
                                    @if($schedule->isNightShift())
                                        <span class="badge px-2 py-1" style="background:#f3e8ff;color:#7e22ce;border:1px solid #d8b4fe;">
                                            <i class="fa-solid fa-moon me-1"></i> Night Shift
                                        </span>
                                    @elseif($schedule->shift_type === 'extra')
                                        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1">
                                            <i class="fa-solid fa-bolt me-1"></i> Extra Shift
                                        </span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                            <i class="fa-solid fa-sun me-1"></i> Day Shift
                                        </span>
                                    @endif
                                    @if($schedule->rate_multiplier > 1.0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ number_format($schedule->rate_multiplier, 2) }}x Multiplier</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end py-3">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary py-1 px-2">
                                        <i class="fa-solid fa-users me-1"></i>{{ $schedule->employees_count ?? $schedule->employees->count() }}
                                    </span>
                                    <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('schedule.edit', $schedule->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.schedule.edit', $schedule->id) : (Auth::user()->role->slug === 'hr-manager' ? route('hr.schedule.edit', $schedule->id) : route('moderator.schedule.edit', $schedule->id) )) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3" title="Edit Schedule">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Edit
                                    </a>
                                    <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('schedule.destroy', $schedule->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.schedule.destroy', $schedule->id) : (Auth::user()->role->slug === 'hr-manager' ? route('hr.schedule.destroy', $schedule->id) : route('moderator.schedule.destroy', $schedule->id) )) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this schedule?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="Delete Schedule">
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
        </div>
    </div>
</section>
@endsection