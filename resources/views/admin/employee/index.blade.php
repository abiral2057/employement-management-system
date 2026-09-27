@extends('layouts.admin')

@section('title')
    {{ __('Manage Employees') }}
@endsection

@section('header')
  <div class="page-header d-flex align-items-center justify-content-between mb-4 pb-3 flex-wrap gap-3">
    <div>
      <h1 class="h1 mb-2">
        <i class="fa-solid fa-users-viewfinder text-dark me-2"></i>{{ __('Manage Employees') }}
      </h1>
      <p class="text-muted fs-6 mb-0">Overview of all active staff, roles, departments, and payroll schedules.</p>
    </div>
    <div class="d-flex gap-3 flex-wrap">
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}" class="btn btn-outline-primary">
        <i class="fas fa-search me-1"></i> Search Employee
      </a>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.create') : route('hr.employee.create')) }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Employee
      </a>
    </div>
  </div>
@endsection

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card flex-fill border-0 shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">
          <i class="fas fa-table me-2 text-muted"></i>Employee Directory
        </h5>
        <span class="badge badge-yellowgreen">{{ $employees->count() }} employees</span>
      </div>
      <table class="table data-table mb-0 align-middle">
        <thead>
          <tr>
            <th style="width: 70px;">#</th>
            <th>{{ __('Employee Name') }}</th>
            <th class="text-end" style="width: 150px;">{{ __('Profile') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach($employees as $employee)
          @php
            $profileUrl = Auth::user()->role->slug === 'super-admin' 
              ? route('employee.show', $employee->id) 
              : (Auth::user()->role->slug === 'administrator' 
                ? route('admin.employee.show', $employee->id) 
                : route('hr.employee.show', $employee->id));
            $initials = strtoupper(substr($employee->firstname, 0, 1) . substr($employee->lastname, 0, 1));
          @endphp
          <tr class="emp-directory-row" style="cursor: pointer;" onclick="window.location='{{ $profileUrl }}'">
            <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
            <td class="py-3">
              <a href="{{ $profileUrl }}" class="d-flex align-items-center text-decoration-none py-1 emp-name-group">
                @if($employee->avatar && file_exists(public_path($employee->avatar)))
                  <img src="{{ asset($employee->avatar) }}" alt="{{ $employee->firstname }}" class="rounded-circle me-3 flex-shrink-0" style="width: 48px; height: 48px; object-fit: cover; border: 2px solid #e4e4e7;">
                @else
                  <div class="avatar-mono me-3 flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.1rem; font-weight: 750; background: #09090b; color: #ffffff; border-radius: 50%;">
                    {{ $initials }}
                  </div>
                @endif
                <div>
                  <div class="fw-bold fs-4 text-dark emp-name-heading mb-0" style="letter-spacing: -0.025em; line-height: 1.2;">
                    {{ $employee->firstname }} {{ $employee->lastname }}
                  </div>
                  <div class="text-muted small mt-1">
                    <span class="badge bg-light text-muted border py-1 px-2" style="font-weight: 550;">{{ $employee->unique_id }}</span>
                    @if(optional($employee->department)->title)
                      <span class="ms-1 text-secondary">&bull; {{ $employee->department->title }}</span>
                    @endif
                  </div>
                </div>
              </a>
            </td>
            <td class="text-end py-3">
              <a href="{{ $profileUrl }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 py-2 fw-semibold" style="letter-spacing: -0.01em;">
                Profile <i class="fa-solid fa-arrow-right ms-1"></i>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection

@section('script')
<style>
.emp-directory-row {
  transition: background-color 0.15s ease;
}
.emp-directory-row:hover {
  background-color: #f8fafc !important;
}
.emp-directory-row:hover .emp-name-heading {
  color: #09090b !important;
  text-decoration: underline;
  text-underline-offset: 4px;
}
.emp-directory-row:hover .btn-outline-dark {
  background-color: #09090b !important;
  color: #ffffff !important;
}
</style>
@endsection
