@extends('layouts.admin')

@section('title', __('My Profile & Settings'))

@section('header')
  <div class="row mb-4 align-items-center">
    <div class="col-md-8">
      <h1 class="display-6 fw-extrabold text-dark tracking-tight mb-1">
        <i class="fa-solid fa-user-gear me-2"></i>{{ __('My Profile & Account Settings') }}
      </h1>
      <p class="text-secondary fs-6 mb-0">
        {{ __('Manage your personal credentials, role permissions, and authentication security.') }}
      </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
      <span class="badge bg-dark text-white px-3 py-2 rounded-pill fs-6">
        <i class="fa-solid fa-shield-halved text-yellowgreen me-1"></i>
        {{ Auth::user()->role ? Auth::user()->role->name : __('User') }}
      </span>
    </div>
  </div>
@endsection

@section('content')
<div class="row g-4">
  {{-- Left Column: User Summary Card --}}
  <div class="col-lg-4">
    <div class="card border border-light-subtle shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
      <div class="card-body text-center p-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-3 border mb-3" style="width: 96px; height: 96px;">
          <i class="fa-solid fa-user-tie text-dark" style="font-size: 2.75rem;"></i>
        </div>
        <h2 class="h3 fw-bold text-dark mb-1">{{ Auth::user()->name }}</h2>
        <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
        
        <div class="d-flex justify-content-center gap-2 mb-4">
          <span class="badge" style="background-color: #f4f4f5; color: #09090b; border: 1px solid #e4e4e7; font-weight: 700; padding: 6px 12px;">
            <i class="fa-solid fa-id-badge me-1"></i> ID #{{ Auth::id() }}
          </span>
          <span class="badge" style="background-color: rgba(154, 205, 50, 0.15); color: #4d6600; border: 1px solid #9acd32; font-weight: 700; padding: 6px 12px;">
            <i class="fa-solid fa-circle-check me-1"></i> Active
          </span>
        </div>

        <hr class="my-3 border-light-subtle">

        <div class="text-start">
          <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle small">
            <span class="text-muted"><i class="fa-solid fa-user-shield me-2"></i>{{ __('Role') }}</span>
            <span class="fw-bold text-dark">{{ Auth::user()->role ? Auth::user()->role->name : 'N/A' }}</span>
          </div>
          <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle small">
            <span class="text-muted"><i class="fa-solid fa-calendar-days me-2"></i>{{ __('Member Since') }}</span>
            <span class="fw-bold text-dark">{{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</span>
          </div>
          <div class="d-flex justify-content-between py-2 small">
            <span class="text-muted"><i class="fa-solid fa-clock-rotate-left me-2"></i>{{ __('Last Updated') }}</span>
            <span class="fw-bold text-dark">{{ Auth::user()->updated_at ? Auth::user()->updated_at->diffForHumans() : 'Recently' }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Quick Access Card --}}
    <div class="card border border-light-subtle shadow-sm" style="border-radius: 12px; background: #ffffff;">
      <div class="card-header bg-white border-bottom border-light-subtle py-3">
        <h3 class="h5 fw-bold text-dark mb-0"><i class="fa-solid fa-bolt me-2 text-dark"></i>{{ __('Quick Shortcuts') }}</h3>
      </div>
      <div class="list-group list-group-flush" style="border-radius: 0 0 12px 12px;">
        <a href="{{ route('payroll.transactions') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
          <span class="fw-semibold text-dark"><i class="fa-solid fa-receipt me-2 text-secondary"></i>{{ __('Transaction History') }}</span>
          <i class="fa-solid fa-arrow-right text-muted small"></i>
        </a>
        <a href="{{ route('payroll.due') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
          <span class="fw-semibold text-dark"><i class="fa-solid fa-bell me-2 text-secondary"></i>{{ __('Payment Due & Reminders') }}</span>
          <i class="fa-solid fa-arrow-right text-muted small"></i>
        </a>
        <a href="{{ Auth::user()->role && Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role && Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : (Route::has('hr.employee.index') ? route('hr.employee.index') : url('/'))) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
          <span class="fw-semibold text-dark"><i class="fa-solid fa-users me-2 text-secondary"></i>{{ __('Employee Directory') }}</span>
          <i class="fa-solid fa-arrow-right text-muted small"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- Right Column: Profile Forms --}}
  <div class="col-lg-8">
    {{-- Update Profile Information --}}
    <div class="card border border-light-subtle shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
      <div class="card-header bg-white border-bottom border-light-subtle py-3">
        <div class="d-flex align-items-center">
          <div class="bg-light rounded p-2 border me-3">
            <i class="fa-solid fa-address-card text-dark fs-5"></i>
          </div>
          <div>
            <h2 class="h4 fw-bold text-dark mb-0">{{ __('Profile Information') }}</h2>
            <p class="text-muted small mb-0">{{ __("Update your account's display name and primary email address.") }}</p>
          </div>
        </div>
      </div>
      <div class="card-body p-4">
        @if (session('status') === 'profile-updated')
          <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2"></i>
            <div>{{ __('Your profile information has been saved successfully.') }}</div>
          </div>
        @endif

        <form method="post" action="{{ route('profile.update') }}">
          @csrf
          @method('patch')

          <div class="mb-3">
            <label for="name" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">{{ __('Full Name') }}</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
              <input id="name" name="name" type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            </div>
            @error('name')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-4">
            <label for="email" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">{{ __('Email Address') }}</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
              <input id="email" name="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            </div>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
              <i class="fa-solid fa-floppy-disk"></i>
              <span>{{ __('Save Profile') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- Update Password --}}
    <div class="card border border-light-subtle shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
      <div class="card-header bg-white border-bottom border-light-subtle py-3">
        <div class="d-flex align-items-center">
          <div class="bg-light rounded p-2 border me-3">
            <i class="fa-solid fa-lock text-dark fs-5"></i>
          </div>
          <div>
            <h2 class="h4 fw-bold text-dark mb-0">{{ __('Update Password') }}</h2>
            <p class="text-muted small mb-0">{{ __('Ensure your account is using a long, secure password to protect sensitive HR data.') }}</p>
          </div>
        </div>
      </div>
      <div class="card-body p-4">
        @if (session('status') === 'password-updated')
          <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2"></i>
            <div>{{ __('Password updated successfully.') }}</div>
          </div>
        @endif

        <form method="post" action="{{ route('password.update') }}">
          @csrf
          @method('put')

          <div class="mb-3">
            <label for="current_password" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">{{ __('Current Password') }}</label>
            <div class="input-group">
              <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-key"></i></span>
              <input id="current_password" name="current_password" type="password" class="form-control form-control-lg @if(isset($errors) && $errors->getBag('updatePassword')->has('current_password')) is-invalid @endif" autocomplete="current-password" />
            </div>
            @if (isset($errors) && $errors->getBag('updatePassword')->has('current_password'))
              <div class="text-danger small mt-1 fw-semibold">{{ $errors->getBag('updatePassword')->first('current_password') }}</div>
            @endif
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label for="password" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">{{ __('New Password') }}</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                <input id="password" name="password" type="password" class="form-control form-control-lg @if(isset($errors) && $errors->getBag('updatePassword')->has('password')) is-invalid @endif" autocomplete="new-password" />
              </div>
              @if (isset($errors) && $errors->getBag('updatePassword')->has('password'))
                <div class="text-danger small mt-1 fw-semibold">{{ $errors->getBag('updatePassword')->first('password') }}</div>
              @endif
            </div>

            <div class="col-md-6">
              <label for="password_confirmation" class="form-label fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">{{ __('Confirm New Password') }}</label>
              <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-shield-check"></i></span>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control form-control-lg @if(isset($errors) && $errors->getBag('updatePassword')->has('password_confirmation')) is-invalid @endif" autocomplete="new-password" />
              </div>
              @if (isset($errors) && $errors->getBag('updatePassword')->has('password_confirmation'))
                <div class="text-danger small mt-1 fw-semibold">{{ $errors->getBag('updatePassword')->first('password_confirmation') }}</div>
              @endif
            </div>
          </div>

          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
              <i class="fa-solid fa-shield-halved"></i>
              <span>{{ __('Update Password') }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- Danger Zone Card --}}
    <div class="card border border-danger-subtle shadow-sm" style="border-radius: 12px; background: #ffffff;">
      <div class="card-header bg-light border-bottom border-danger-subtle py-3">
        <div class="d-flex align-items-center">
          <div class="bg-white text-danger rounded p-2 border border-danger-subtle me-3">
            <i class="fa-solid fa-triangle-exclamation fs-5"></i>
          </div>
          <div>
            <h2 class="h5 fw-bold text-danger mb-0">{{ __('Account Security Zone') }}</h2>
            <p class="text-muted small mb-0">{{ __('Administrative safety procedures and account deactivation controls.') }}</p>
          </div>
        </div>
      </div>
      <div class="card-body p-4">
        <p class="text-secondary small mb-3">
          {{ __('Once your account is deleted, all HR authorizations, records created by this profile, and system sessions will be permanently purged.') }}
        </p>
        <button type="button" class="btn btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
          <i class="fa-solid fa-trash-can me-1"></i> {{ __('Deactivate Account') }}
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Deletion Confirmation Modal --}}
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold" id="confirmUserDeletionModalLabel">
          <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ __('Confirm Account Deletion') }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')
        <div class="modal-body p-4">
          <p class="text-dark fw-semibold mb-3">
            {{ __('Are you sure you want to delete your account? Please enter your password to confirm permanent deletion.') }}
          </p>
          <div class="mb-3">
            <label for="delete_password" class="form-label fw-bold small text-uppercase">{{ __('Current Password') }}</label>
            <input id="delete_password" name="password" type="password" class="form-control form-control-lg @if(isset($errors) && $errors->getBag('userDeletion')->has('password')) is-invalid @endif" placeholder="{{ __('Password') }}" required />
            @if (isset($errors) && $errors->getBag('userDeletion')->has('password'))
              <div class="text-danger small mt-1">{{ $errors->getBag('userDeletion')->first('password') }}</div>
            @endif
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button type="submit" class="btn btn-danger fw-bold">{{ __('Permanently Delete Account') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
