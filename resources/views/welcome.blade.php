<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name', 'HRMS') }} — Enterprise Management System</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
  <link href="{{ asset('css/typography.css') }}" rel="stylesheet" />
  <style>
    body {
      background-color: #09090b;
      color: #ffffff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .landing-card {
      background: #ffffff;
      color: #09090b;
      border: 1px solid #27272a;
      border-radius: 16px;
      padding: 48px 40px;
      max-width: 520px;
      width: 100%;
      text-align: center;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
    }
    .brand-mark {
      width: 54px;
      height: 54px;
      background: #09090b;
      color: #ffffff;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
    }
    .btn-mono-black {
      background: #09090b;
      color: #ffffff;
      border: 1px solid #09090b;
      border-radius: 8px;
      font-weight: 600;
      padding: 12px 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.15s ease;
      text-decoration: none;
    }
    .btn-mono-black:hover {
      background: #27272a;
      color: #ffffff;
      border-color: #27272a;
    }
    .btn-mono-outline {
      background: transparent;
      color: #09090b;
      border: 1.5px solid #09090b;
      border-radius: 8px;
      font-weight: 600;
      padding: 12px 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: all 0.15s ease;
      text-decoration: none;
    }
    .btn-mono-outline:hover {
      background: #09090b;
      color: #ffffff;
    }
  </style>
</head>
<body>
  <div class="landing-card">
    <div class="brand-mark">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="7" height="7"></rect>
        <rect x="14" y="3" width="7" height="7"></rect>
        <rect x="14" y="14" width="7" height="7"></rect>
        <rect x="3" y="14" width="7" height="7"></rect>
      </svg>
    </div>
    <h1 class="h2 fw-bold text-dark mb-2" style="letter-spacing: -0.03em;">HRMS <span style="font-size: 0.75rem; font-weight: 700; padding: 2px 8px; background: #09090b; color: #fff; border-radius: 4px; vertical-align: middle;">ENTERPRISE</span></h1>
    <p class="text-muted small mb-4">High-Precision Employee Tracking, Shift Roster, Overtime & Payment Management</p>

    <div class="d-grid gap-2">
      @auth
        <a href="{{ url('/dashboard') }}" class="btn-mono-black">
          <i class="fa-solid fa-gauge"></i> Enter Dashboard
        </a>
      @else
        <a href="{{ route('login') }}" class="btn-mono-black">
          <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In to System
        </a>
        @if (Route::has('register'))
          <a href="{{ route('register') }}" class="btn-mono-outline mt-2">
            Create Account
          </a>
        @endif
      @endauth
    </div>

    <div class="mt-4 pt-3 border-top text-muted small d-flex justify-content-between align-items-center">
      <span><i class="fa-solid fa-shield-halved me-1"></i> Monochrome System UI</span>
      <span class="badge bg-light text-dark border">v2.4.0</span>
    </div>
  </div>
</body>
</html>
