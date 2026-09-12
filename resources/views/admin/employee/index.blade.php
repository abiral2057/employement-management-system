@extends('layouts.admin')

@section('title')
    {{ __('Manage Employees') }}
@endsection

@section('header')
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">
      <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Manage Employees
    </h1>
    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search')) }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-search me-1"></i> Search Employee
      </a>
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.create') : route('hr.employee.create')) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Add New
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
          <i class="fas fa-table me-2 text-muted"></i>Employee DataTable
        </h5>
        <span class="badge bg-primary">{{ $employees->count() }} employees</span>
      </div>
      <table class="table data-table mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Employee ID</th>
            <th>Name</th>
            <th>Department</th>
            <th>Designation</th>
            <th>Schedule</th>
            <th>Status</th>
            <th>Date Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($employees as $employee)
          <tr>
            <td class="text-muted">{{ $loop->iteration }}</td>
            <td>
              <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.8rem;">
                {{ $employee->unique_id }}
              </span>
            </td>
            <td>
              <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.show', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.show', $employee->id) : route('hr.employee.show', $employee->id)) }}"
                 class="text-decoration-none text-dark fw-semibold emp-name-link">
                {{ $employee->firstname }} {{ $employee->lastname }}
                <i class="fas fa-external-link-alt ms-1 text-muted" style="font-size:.7rem;opacity:.5;"></i>
              </a>
            </td>
            <td>{{ optional($employee->department)->title ?? '—' }}</td>
            <td>{{ optional($employee->designation)->title ?? '—' }}</td>
            <td>{{ optional($employee->schedule)->title ?? '—' }}</td>
            <td>
              @if($employee->status == 1)
                <span class="badge bg-success">Active</span>
              @else
                <span class="badge bg-secondary">Inactive</span>
              @endif
            </td>
            <td>{{ $employee->created_at->diffForHumans() }}</td>
            <td>
              <div class="d-flex align-items-center gap-1">
                {{-- View Full Profile --}}
                <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.show', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.show', $employee->id) : route('hr.employee.show', $employee->id)) }}"
                   class="btn btn-success btn-sm" title="View Full Profile">
                  <i class="fas fa-eye"></i>
                </a>
                {{-- Edit --}}
                <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.edit', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.edit', $employee->id) : route('hr.employee.edit', $employee->id)) }}"
                   class="btn btn-info btn-sm" title="Edit Employee">
                  <i class="fas fa-edit"></i>
                </a>
                {{-- Delete --}}
                <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.destroy', $employee->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.destroy', $employee->id) : route('hr.employee.destroy', $employee->id)) }}" method="post">
                  <a href="#" class="btn btn-danger btn-sm" title="Delete Employee" onclick="del(event, this)">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </form>
              </div>
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
.emp-name-link:hover { color: #3b7ddd !important; }
.emp-name-link:hover .fa-external-link-alt { opacity: 1 !important; }
</style>
@endsection
