<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta name="description" content="Employee Management System - HRMS">
	<meta name="author" content="Mohona Akter Nabila & Shorifa Akter" />
	<title>@yield('title') | {{ config('app.name') }}</title>

	<link rel="shortcut icon" href="{{asset('img/icons/icon-48x48.png')}}" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

	<link href="{{asset('css/app.css')}}" rel="stylesheet">
	<link href="{{asset('css/satoshi.css')}}" rel="stylesheet">
	<link href="{{asset('css/typography.css')}}" rel="stylesheet">
	<link href="{{asset('css/custom.css')}}" rel="stylesheet">
</head>

<body>
	<div class="auth-wrapper">
		<!-- Monochrome Graphic Brand Panel -->
		<div class="auth-brand d-none d-lg-flex">
			<div class="login-brand-logo">
				<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#09090b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="3" width="7" height="7"></rect>
					<rect x="14" y="3" width="7" height="7"></rect>
					<rect x="14" y="14" width="7" height="7"></rect>
					<rect x="3" y="14" width="7" height="7"></rect>
				</svg>
			</div>
			<h2>{{ config('app.name', 'HRMS PRO') }}</h2>
			<p>Streamline your workforce management with our comprehensive HRMS solution.</p>
			<div class="mt-4" style="display: flex; gap: 1.5rem; opacity: 0.7;">
				<div class="text-center">
					<i class="fas fa-user-check" style="font-size: 1.5rem;"></i>
					<div style="font-size: 0.8rem; margin-top: 0.3rem;">Employee Mgmt</div>
				</div>
				<div class="text-center">
					<i class="fas fa-calendar-check" style="font-size: 1.5rem;"></i>
					<div style="font-size: 0.8rem; margin-top: 0.3rem;">Attendance</div>
				</div>
				<div class="text-center">
					<i class="fas fa-wallet" style="font-size: 1.5rem;"></i>
					<div style="font-size: 0.8rem; margin-top: 0.3rem;">Payroll</div>
				</div>
			</div>
		</div>

		<!-- Login Content -->
		<div class="auth-content">
			<div>
				@yield('header')
				@yield('content')
			</div>
		</div>
	</div>

	<script src="{{asset('js/app.js')}}"></script>

</body>

</html>