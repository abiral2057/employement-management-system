@extends('layouts.admin')

@section('title')
  {{ __('Manage Departments') }}
@endsection

@section('header')
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="display-6 fw-extrabold text-dark tracking-tight mb-1">{{ __('Manage Departments') }}</h1>
      <p class="text-secondary small mb-0">{{ __('Organize company divisions, structural units, and functional teams') }}</p>
    </div>
    <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('department.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.department.create') : route('hr.department.create') ) }}" class="btn btn-primary px-3 py-2 fw-bold shadow-sm d-inline-flex align-items-center">
      <i class="fas fa-plus me-2"></i>
      <span>{{ __('Add Department') }}</span>
    </a>
  </div>
@endsection

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card flex-fill border border-light-subtle shadow-sm" style="border-radius: 12px;">
      <div class="card-body p-4">
        <table class="table data-table align-middle mb-0">
          <thead>
            <tr>
              <th scope="col" style="width: 70px;">#</th>
              <th scope="col">{{ __('Department') }}</th>
              <th scope="col" class="text-end" style="width: 140px;">{{ __('Action') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($departments as $department)
              <tr>
                <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                <td class="py-3">
                  <div class="fw-bold fs-4 text-dark mb-1" style="letter-spacing: -0.025em; line-height: 1.2;">
                    {{ $department->title }}
                  </div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border py-1 px-2">
                      <i class="fa-solid fa-users me-1 text-secondary"></i>{{ $department->employees_count ?? 0 }} Staff
                    </span>
                    @if ($department->status === 1)
                      <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Active</span>
                    @elseif ($department->status === 0)
                      <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Disabled</span>
                    @else
                      <span class="badge bg-secondary bg-opacity-10 text-secondary">Pending</span>
                    @endif
                    <span class="text-muted small">&bull; Created {{ $department->created_at->diffForHumans() }}</span>
                  </div>
                </td>
                <td class="text-end py-3">
                  <div class="d-inline-flex gap-1">
                    <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('department.edit', $department->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.department.edit', $department->id) : route('hr.department.edit', $department->id) ) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3" title="{{ __('Edit') }}">
                      <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('department.destroy', $department->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.department.destroy', $department->id) : route('hr.department.destroy', $department->id) ) }}" method="post" class="d-inline">
                      @csrf
                      @method('delete')
                      <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-2" onclick="del(event, this)" title="{{ __('Delete') }}">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center py-5 text-muted">{{ __('No Departments Found') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection

@section('script')
@endsection
