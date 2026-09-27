@extends('layouts.auth')

@section('title')
  {{ __('Login') }}
@endsection

@section('header')
  <div class="text-center mb-4">
    <h1 class="display-6 fw-extrabold text-dark tracking-tight mb-2">{{ __('Sign In') }}</h1>
    <p class="text-secondary fs-5 mb-0">
      {{ __('Access your employee management dashboard') }}
    </p>
  </div>
@endsection

@section('content')
  <x-auth-session-status class="mb-4" :status="session('status')" />
  
  <div class="card border border-light-subtle shadow-sm" style="border-radius: 14px; background: #ffffff;">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle p-3 border mb-3" style="width: 72px; height: 72px;">
          <i class="fa-solid fa-user-shield text-dark fs-2"></i>
        </div>
        <h2 class="h4 fw-bold text-dark mb-1">{{ __('Account Authentication') }}</h2>
        <p class="text-muted small mb-0">{{ __('Enter your corporate credentials below') }}</p>
      </div>

      <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="mb-3">
          <label class="form-label fw-bold text-dark text-uppercase small" style="letter-spacing: 0.05em;">{{ __('Email Address') }}</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
            <input class="form-control form-control-lg border-start-0 ps-0" type="email" name="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus />
          </div>
          @error('email')
            <span class="text-danger small mt-1 d-block fw-semibold">{{ $message }}</span>
          @enderror
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label fw-bold text-dark text-uppercase small mb-0" style="letter-spacing: 0.05em;">{{ __('Password') }}</label>
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="small fw-semibold text-dark text-decoration-underline">{{ __('Forgot password?') }}</a>
            @endif
          </div>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
            <input class="form-control form-control-lg border-start-0 ps-0" type="password" name="password" placeholder="••••••••" required autocomplete="current-password" />
          </div>
          @error('password')
            <span class="text-danger small mt-1 d-block fw-semibold">{{ $message }}</span>
          @enderror
        </div>

        <div class="mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" checked>
            <label class="form-check-label text-secondary small" for="remember-me">
              {{ __('Keep me signed in on this device') }}
            </label>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
            <span>{{ __('Sign In to Dashboard') }}</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection