@extends('layouts.admin')

@section('title')
    {{ __('Create Shift Schedule') }}
@endsection

@section('header')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-0">{{ __('Create Shift Schedule') }}</h1>
        <p class="text-muted small mb-0">{{ __('Define Day, Night (cross-midnight), or Extra shift timing template.') }}</p>
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
                    <i class="fa-solid fa-clock text-primary me-2"></i>{{ __('Shift Details') }}
                </h5>
            </div>
            <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('schedule.store') : (Auth::user()->role->slug === 'administrator' ? route('admin.schedule.store') : route('moderator.schedule.store') ) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Schedule / Shift Title') }} <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Day Shift-A, Night Shift-01, Weekend Extra" value="{{ old('title') }}" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Shift Category') }} <span class="text-danger">*</span></label>
                        <select name="shift_type" class="form-select" id="shiftTypeSelect">
                            <option value="day" {{ old('shift_type') === 'day' ? 'selected' : '' }}>☀️ Day Shift</option>
                            <option value="night" {{ old('shift_type') === 'night' ? 'selected' : '' }}>🌙 Night Shift (Cross-Midnight)</option>
                            <option value="extra" {{ old('shift_type') === 'extra' ? 'selected' : '' }}>⚡ Extra / Split Shift</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Time In (Start)') }} <span class="text-danger">*</span></label>
                            <input type="time" name="time_in" id="timeInInput" class="form-control" value="{{ old('time_in', '09:00') }}" required />
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">{{ __('Time Out (End)') }} <span class="text-danger">*</span></label>
                            <input type="time" name="time_out" id="timeOutInput" class="form-control" value="{{ old('time_out', '17:00') }}" required />
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="cross_midnight" value="1" id="crossMidnightCheck" {{ old('cross_midnight') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="crossMidnightCheck">
                            <strong>{{ __('Crosses Midnight (+1 Day)') }}</strong>
                            <div class="text-muted" style="font-size: .78rem;">Check this if the shift ends on the next calendar day (e.g. 10:00 PM to 6:00 AM).</div>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">{{ __('Rate Multiplier (for Extra / Night Shifts)') }}</label>
                        <input type="number" step="0.05" min="1.0" max="3.0" name="rate_multiplier" class="form-control" value="{{ old('rate_multiplier', '1.00') }}" />
                        <div class="text-muted small">Standard is 1.00; 1.25 or 1.50 for night differential or extra duty.</div>
                    </div>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between py-3">
                    <a href="{{ route('schedule.index') }}" class="btn btn-outline-secondary btn-sm">Discard</a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> Save Shift Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>
const timeIn = document.getElementById('timeInInput');
const timeOut = document.getElementById('timeOutInput');
const crossCheck = document.getElementById('crossMidnightCheck');
const shiftType = document.getElementById('shiftTypeSelect');

function checkNight() {
    if (timeIn.value && timeOut.value) {
        if (timeOut.value < timeIn.value) {
            crossCheck.checked = true;
            if (shiftType.value === 'day') shiftType.value = 'night';
        }
    }
}
timeIn?.addEventListener('change', checkNight);
timeOut?.addEventListener('change', checkNight);
</script>
@endsection