@extends('layouts.admin')

@section('title')
  {{ __('Manage Roles') }}
@endsection

@section('header')
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3">{{ __('Manage Roles') }}</h1>
    <a href="{{ route('roles.create') }}" class="btn btn-primary">
      <i class="fas fa-plus"></i>
      <span class="ps-1">{{ __('Add new') }}</span>
    </a>
  </div>
@endsection

@section('content')
  <section class="row">
    <div class="col-12">
      <div class="card flex-fill">
        <div class="card-header">              
          <h5 class="card-title mb-0">{{ __('Roles DataTable') }}</h5>
        </div>
        <table class="table table-hover align-middle my-0 data-table">
          <thead class="table-light text-uppercase small text-muted">
            <tr>
              <th style="width: 50px;">#</th>
              <th>{{ __('Role Title & Identity') }}</th>
              <th class="text-end" style="width: 120px;">{{ __('Action') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($roles as $k => $role)
              <tr>
                <td class="text-muted fw-bold">{{ $k + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <span class="fw-bold fs-5 text-dark">{{ $role->title }}</span>
                    <span class="badge bg-light text-dark border"><code>{{ $role->slug }}</code></span>
                    @if ($role->status === 1)
                      <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Active</span>
                    @elseif ($role->status === 0)
                      <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Disabled</span>
                    @else
                      <span class="badge bg-secondary">Pending</span>
                    @endif
                  </div>
                  <div class="small text-muted">
                    Created {{ $role->created_at->diffForHumans() }}
                  </div>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-1">
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-outline-primary btn-sm px-2" title="Edit Role">
                      <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('roles.destroy', $role->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this role?');">
                      @csrf
                      @method("delete")
                      <button type="submit" class="btn btn-outline-danger btn-sm px-2" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
            <tr>
              <td colspan="3" class="text-center py-4 text-muted">
                {{ __('No data found') }}
              </td>
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