@extends('layouts.admin')

@section('title')
    {{ __('Edit Shift Schedule') }}
@endsection

@section('header')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0">{{ __('Update Shift Schedule') }}</h1>
        <p class="text-muted small mb-0">{{ __('Edit shift timings, Day/Night categorization, or extra shift parameters.') }}</p>
    </div>
    <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Schedules
    </a>
</div>
@endsection

@section('content')
<section class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="fa-solid fa-clock text-primary me-2"></i>{{ __('Update Details') }} &bull; {{ $schedule->title }}
                </h5>
            </div>
            <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('schedule.update', $schedule->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.schedule.update', $schedule->id) : route('moderator.schedule.update', $schedule->id)) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Schedule Title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $schedule->title) }}" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Shift Category') }} <span class="text-danger">*</span></label>
                        <select name="shift_type" class="form-select" id="shiftTypeSelect">
                            <option value="day" {{ old('shift_type', $schedule->shift_type) === 'day' ? 'selected' : '' }}>☀️ Day Shift</option>
                            <option value="night" {{ old('shift_type', $schedule->shift_type) === 'night' ? 'selected' : '' }}>🌙 Night Shift (Cross-Midnight)</option>
                            <option value="extra" {{ old('shift_type', $schedule->shift_type) === 'extra' ? 'selected' : '' }}>⚡ Extra / Split Shift</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Time In (Start)') }} <span class="text-danger">*</span></label>
                            <input type="time" name="time_in" id="timeInInput" class="form-control" value="{{ old('time_in', $schedule->time_in) }}" required />
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Time Out (End)') }} <span class="text-danger">*</span></label>
                            <input type="time" name="time_out" id="timeOutInput" class="form-control" value="{{ old('time_out', $schedule->time_out) }}" required />
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="cross_midnight" value="1" id="crossMidnightCheck" {{ old('cross_midnight', $schedule->cross_midnight || $schedule->isNightShift()) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="crossMidnightCheck">
                            <strong>{{ __('Crosses Midnight (+1 Day)') }}</strong>
                            <div class="text-muted" style="font-size: .78rem;">Shift completes on the next calendar day.</div>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Rate Multiplier (for Extra / Night Shifts)') }}</label>
                        <input type="number" step="0.05" min="1.0" max="3.0" name="rate_multiplier" class="form-control" value="{{ old('rate_multiplier', $schedule->rate_multiplier ?? '1.00') }}" />
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                    <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection