@extends('layouts.admin')

@section('title')
    {{ __('Manage Salary') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-wallet text-dark me-2"></i>{{ __('Salary Structure Management') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Base compensation packages, standard allowances, and staff pay structures.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('salary.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.salary.create') : route('payroll.salary.create') ) }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="fas fa-plus me-1"></i> {{ __('Add New Salary') }}
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">
            <i class="fa-solid fa-list-check me-2 text-primary"></i>{{ __('Staff Salary Directory') }}
        </h5>
        <span class="badge bg-light text-dark border">{{ count($salaries) }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 data-table">
            <thead class="table-light text-uppercase small text-muted">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>{{ __('Employee') }}</th>
                    <th>{{ __('Base Compensation & Allowances') }}</th>
                    <th class="text-end" style="width: 140px;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($salaries as $salary)
                    <tr>
                        <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-mono flex-shrink-0" style="width:40px;height:40px;background:#09090b;color:#ffffff;font-size:.85rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    {{ strtoupper(substr($salary->employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($salary->employee->lastname ?? 'M', 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.employee.show', $salary->employee->id ?? 1) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                                        {{ $salary->employee->firstname ?? '' }} {{ $salary->employee->lastname ?? '' }}
                                    </a>
                                    <div class="text-muted small">
                                        <span class="badge bg-light text-dark border me-1">{{ $salary->employee->unique_id ?? '—' }}</span>
                                        <span>{{ optional($salary->employee->department)->title ?? 'General' }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="fw-bold fs-5 text-primary">€ {{ number_format($salary->basic ?? 0, 2) }}</span>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Basic</span>
                                @if($salary->house_rent > 0)
                                    <span class="badge bg-light text-dark border">+ Rent: €{{ number_format($salary->house_rent, 2) }}</span>
                                @endif
                                @if($salary->medical > 0)
                                    <span class="badge bg-light text-dark border">+ Med: €{{ number_format($salary->medical, 2) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('salary.edit', $salary->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.salary.edit', $salary->id) : route('payroll.salary.edit', $salary->id) ) }}" class="btn btn-outline-primary btn-sm px-2" title="Edit Salary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.employee.show', $salary->employee->id ?? 1) }}#pane-salary" class="btn btn-outline-secondary btn-sm px-2" title="View Full Package">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection