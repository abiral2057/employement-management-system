@extends('layouts.admin')

@section('title')
    {{ __('Manage user') }}
@endsection

@section('header')
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3">{{ __('Manage user') }}</h1>
    <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('user.create') : route('admin.users.create')  }}" class="btn btn-primary">
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
          <h5 class="card-title mb-0">{{ __('User DataTable') }}</h5>
        </div>
        <table class="table table-hover align-middle my-0 data-table">
          <thead>
            <tr>
              <th style="width: 70px;">#</th>
              <th>{{ __('System User') }}</th>
              <th class="text-end" style="width: 140px;">{{ __('Action') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($user as $k => $u)
              @php
                $initials = strtoupper(substr($u->name, 0, 1));
              @endphp
              <tr>
                <td class="text-muted fw-bold">{{ $k + 1 }}</td>
                <td class="py-3">
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-mono flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.05rem; background: #09090b; color: #fff; border-radius: 50%;">
                      {{ $initials }}
                    </div>
                    <div>
                      <div class="fw-bold fs-4 text-dark mb-1" style="letter-spacing: -0.025em; line-height: 1.2;">
                        {{ $u->name }}
                      </div>
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-muted small">{{ $u->email }}</span>
                        @if($u->phone)
                          <span class="text-secondary small">&bull; {{ $u->phone }}</span>
                        @endif
                        <span class="badge bg-primary bg-opacity-10 text-primary py-1 px-2">{{ optional($u->role)->title ?? 'User' }}</span>
                        @if ($u->status === 1)
                          <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Active</span>
                        @elseif ($u->status === 0)
                          <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Disabled</span>
                        @else
                          <span class="badge bg-secondary bg-opacity-10 text-secondary">Pending</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </td>
                <td class="text-end py-3">
                  <div class="d-inline-flex gap-1">
                    <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('user.edit', $u->id) : route('admin.users.edit', $u->id) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3" title="Edit User">
                      <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('user.destroy', $u->id) : route('admin.users.destroy', $u->id)}}" method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                      @csrf
                      @method("delete")
                      <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle p-1" style="width: 32px; height: 32px;" title="Delete User">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center">{{ __('No data found') }}</td>
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