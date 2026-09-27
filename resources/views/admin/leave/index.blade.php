@extends('layouts.admin')

@section('title')
  {{ __('Manage Leaves & Requests') }}
@endsection

@section('header')
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="h2 fw-bold text-dark mb-1">
        <i class="fas fa-person-walking-arrow-right text-primary me-2"></i>{{ __('Leave Management') }}
      </h1>
      <p class="text-secondary small mb-0">{{ __('Track employee time-off applications, approval workflow, and leave quotas') }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('leaves.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.leaves.create') : route('hr.leaves.create') ) }}" class="btn btn-primary px-3 py-2 fw-semibold shadow-sm d-inline-flex align-items-center">
        <i class="fas fa-plus me-2"></i>
        <span>{{ __('Apply For Leave') }}</span>
      </a>
    </div>
  </div>

  {{-- Summary Metric Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small text-uppercase fw-bold">All Requests</div>
            <div class="fs-4 fw-bold text-dark">{{ $leaves->count() }}</div>
          </div>
          <div class="stat-icon bg-light text-dark p-3 rounded-circle">
            <i class="fa-solid fa-list-check fs-5"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-warning border-4">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small text-uppercase fw-bold">Pending Review</div>
            <div class="fs-4 fw-bold text-warning">{{ $pendingCount ?? $leaves->where('status', 0)->count() }}</div>
          </div>
          <div class="stat-icon bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
            <i class="fa-solid fa-clock-rotate-left fs-5"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-success border-4">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small text-uppercase fw-bold">Approved</div>
            <div class="fs-4 fw-bold text-success">{{ $approvedCount ?? $leaves->where('status', 1)->count() }}</div>
          </div>
          <div class="stat-icon bg-success bg-opacity-10 text-success p-3 rounded-circle">
            <i class="fa-solid fa-circle-check fs-5"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100 bg-white border-start border-danger border-4">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small text-uppercase fw-bold">Rejected</div>
            <div class="fs-4 fw-bold text-danger">{{ $rejectedCount ?? $leaves->where('status', 2)->count() }}</div>
          </div>
          <div class="stat-icon bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
            <i class="fa-solid fa-circle-xmark fs-5"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card flex-fill border-0 shadow-sm" style="border-radius: 12px;">
      <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0 fw-bold">
          <i class="fa-solid fa-table-list me-2 text-primary"></i>Leave Applications Register
        </h5>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
          <table class="table data-table align-middle table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" style="width: 60px;">#</th>
                <th scope="col">{{ __('Employee & Leave Request') }}</th>
                <th scope="col">{{ __('Period & Status') }}</th>
                <th scope="col" class="text-end" style="width: 170px;">{{ __('Action') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($leaves as $leave)
                @php
                  $leaveTypesMap = [
                    1 => ['name' => 'Vacation', 'color' => '#3b82f6'],
                    2 => ['name' => 'Sick Leave', 'color' => '#ef4444'],
                    3 => ['name' => 'Emergency', 'color' => '#f97316'],
                    4 => ['name' => 'Involuntary', 'color' => '#64748b'],
                    5 => ['name' => 'Medical', 'color' => '#ec4899'],
                    6 => ['name' => 'Casual', 'color' => '#10b981'],
                    7 => ['name' => 'Marriage', 'color' => '#8b5cf6'],
                  ];
                  $ltInfo = $leaveTypesMap[$leave->leave_type] ?? ['name' => 'General Leave', 'color' => '#6b7280'];
                  $start = \Carbon\Carbon::parse($leave->start_date);
                  $end = \Carbon\Carbon::parse($leave->end_date);
                  $duration = $start->diffInDays($end) + 1;
                  $empShowUrl = Auth::user()->role->slug === 'super-admin' 
                    ? route('employee.show', $leave->employee_id) 
                    : (Auth::user()->role->slug === 'administrator' 
                      ? route('admin.employee.show', $leave->employee_id) 
                      : route('hr.employee.show', $leave->employee_id));
                @endphp
                <tr>
                  <td class="fw-bold text-muted">{{ $loop->iteration }}</td>
                  <td class="py-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; font-size: .95rem;">
                        {{ strtoupper(substr(optional($leave->employee)->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr(optional($leave->employee)->lastname ?? 'M', 0, 1)) }}
                      </div>
                      <div>
                        <a href="{{ $empShowUrl }}" class="fw-bold fs-5 text-dark text-decoration-none d-block" style="letter-spacing: -0.02em;">
                          {{ optional($leave->employee)->firstname }} {{ optional($leave->employee)->lastname }}
                        </a>
                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                          <span class="badge border py-1 px-2" style="background-color: {{ $ltInfo['color'] }}15; color: {{ $ltInfo['color'] }}; border-color: {{ $ltInfo['color'] }}30 !important; font-size: 0.76rem;">
                            {{ $ltInfo['name'] }}
                          </span>
                          <span class="text-dark small fw-semibold">{{ $leave->title }}</span>
                          @if($leave->leave_reason)
                            <span class="text-muted small">&bull; {{ Str::limit($leave->leave_reason, 40) }}</span>
                          @endif
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3">
                    <div class="fw-bold fs-6 text-dark mb-1">
                      {{ $start->format('d M') }} &ndash; {{ $end->format('d M, Y') }}
                      <span class="badge bg-light text-dark border ms-1 py-1">{{ $duration }} day{{ $duration > 1 ? 's' : '' }}</span>
                    </div>
                    <div>
                      @if ($leave->status == 1)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-check me-1"></i>Approved</span>
                      @elseif ($leave->status == 2)
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-xmark me-1"></i>Rejected</span>
                      @else
                        <span class="badge bg-warning bg-opacity-15 text-dark border border-warning"><i class="fa-solid fa-clock me-1"></i>Pending Review</span>
                      @endif
                      <span class="text-muted small ms-1">&bull; Applied {{ $leave->created_at ? $leave->created_at->diffForHumans() : 'recently' }}</span>
                    </div>
                  </td>
                  <td class="text-end py-3">
                    <div class="d-inline-flex gap-1 align-items-center">
                      @if($leave->status == 0)
                        {{-- 1-Click Approve --}}
                        <form action="{{ route('leaves.quick.approve', $leave->id) }}" method="POST" class="d-inline">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold" title="Approve Leave">
                            <i class="fa-solid fa-check me-1"></i>Approve
                          </button>
                        </form>
                        {{-- 1-Click Reject --}}
                        <form action="{{ route('leaves.quick.reject', $leave->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Reject this leave request?');">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 32px; height: 32px;" title="Reject">
                            <i class="fa-solid fa-xmark"></i>
                          </button>
                        </form>
                      @endif

                      <a href="{{ Auth::user()->role->slug === 'super-admin' ? route('leaves.edit', $leave->id) : (Auth::user()->role->slug === 'administrator' ? route('admin.leaves.edit', $leave->id) : route('hr.leaves.edit', $leave->id) ) }}" class="btn btn-outline-dark btn-sm rounded-circle p-1" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;" title="{{ __('Edit') }}">
                        <i class="fas fa-edit"></i>
                      </a>
                    </div>
                  </td>
                </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                  <i class="fa-solid fa-calendar-xmark fa-3x mb-3 opacity-25"></i>
                  <p class="mb-0 fw-semibold">{{ __('No Leave Applications Found') }}</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
@endsection

@section('script')
@endsection