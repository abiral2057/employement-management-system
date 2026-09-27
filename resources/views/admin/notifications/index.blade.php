@extends('layouts.admin')

@section('title')
    {{ __('Notification Center & Alerts') }}
@endsection

@section('header')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 gap-3">
    <div>
        <h1 class="h1 mb-2">
            <i class="fa-solid fa-bell text-dark me-2"></i>{{ __('Notification Center') }}
        </h1>
        <p class="text-muted fs-6 mb-0">
            Real-time action alerts, pending approvals, overdue salary warnings, and compliance notifications.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($counts['unread'] > 0)
        <form action="{{ route('notifications.clear-all') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-dark fw-bold shadow-sm">
                <i class="fa-solid fa-check-double me-1"></i> {{ __('Mark All as Read') }}
            </button>
        </form>
        @endif
        <a href="{{ route('notifications.index') }}" class="btn btn-primary fw-bold shadow-sm">
            <i class="fa-solid fa-rotate me-1"></i> {{ __('Refresh') }}
        </a>
    </div>
</div>
@endsection

@section('content')

{{-- ── Category Filter Pills ── --}}
<div class="d-flex gap-2 flex-wrap mb-4">
    <a href="{{ route('notifications.index', ['category' => 'all', 'status' => $status]) }}" 
       class="btn btn-sm {{ $category === 'all' ? 'btn-dark' : 'btn-outline-secondary bg-white' }} fw-bold">
        {{ __('All Alerts') }} <span class="badge bg-secondary ms-1">{{ $counts['all'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['category' => 'all', 'status' => 'unread']) }}" 
       class="btn btn-sm {{ $status === 'unread' ? 'btn-danger' : 'btn-outline-danger bg-white' }} fw-bold">
        <i class="fa-solid fa-circle-dot me-1"></i>{{ __('Unread') }} <span class="badge bg-danger ms-1">{{ $counts['unread'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['category' => 'leave', 'status' => $status]) }}" 
       class="btn btn-sm {{ $category === 'leave' ? 'btn-dark' : 'btn-outline-secondary bg-white' }} fw-bold">
        <i class="fa-solid fa-person-walking-arrow-right me-1 text-warning"></i>{{ __('Leaves') }} <span class="badge bg-light text-dark border ms-1">{{ $counts['leave'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['category' => 'overtime', 'status' => $status]) }}" 
       class="btn btn-sm {{ $category === 'overtime' ? 'btn-dark' : 'btn-outline-secondary bg-white' }} fw-bold">
        <i class="fa-solid fa-stopwatch me-1 text-warning"></i>{{ __('Overtime') }} <span class="badge bg-light text-dark border ms-1">{{ $counts['overtime'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['category' => 'payroll', 'status' => $status]) }}" 
       class="btn btn-sm {{ $category === 'payroll' ? 'btn-dark' : 'btn-outline-secondary bg-white' }} fw-bold">
        <i class="fa-solid fa-file-invoice-dollar me-1 text-danger"></i>{{ __('Payroll & Dues') }} <span class="badge bg-light text-dark border ms-1">{{ $counts['payroll'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['category' => 'document', 'status' => $status]) }}" 
       class="btn btn-sm {{ $category === 'document' ? 'btn-dark' : 'btn-outline-secondary bg-white' }} fw-bold">
        <i class="fa-solid fa-file-shield me-1 text-info"></i>{{ __('Documents') }} <span class="badge bg-light text-dark border ms-1">{{ $counts['document'] }}</span>
    </a>
    <a href="{{ route('notifications.index', ['category' => 'attendance', 'status' => $status]) }}" 
       class="btn btn-sm {{ $category === 'attendance' ? 'btn-dark' : 'btn-outline-secondary bg-white' }} fw-bold">
        <i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>{{ __('Attendance') }} <span class="badge bg-light text-dark border ms-1">{{ $counts['attendance'] }}</span>
    </a>
</div>

{{-- ── Notification Cards List ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if(count($notifications) > 0)
        <div class="list-group list-group-flush">
            @foreach($notifications as $item)
            <div class="list-group-item p-4 {{ !$item['is_read'] ? 'bg-light bg-opacity-25' : '' }} border-bottom">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <div class="flex-shrink-0 mt-1">
                            <span class="badge rounded-3 p-3 bg-{{ $item['color'] }} bg-opacity-10 text-{{ $item['color'] }} border border-{{ $item['color'] }}">
                                <i class="{{ $item['icon'] }} fs-5"></i>
                            </span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <h5 class="mb-0 fw-bold text-dark">{{ $item['title'] }}</h5>
                                <span class="badge bg-{{ $item['color'] }} bg-opacity-10 text-{{ $item['color'] }} border border-{{ $item['color'] }} px-2 py-1">
                                    {{ $item['badge'] }}
                                </span>
                                @if(!$item['is_read'])
                                    <span class="badge bg-danger text-white px-2 py-0" style="font-size: 0.65rem;">NEW</span>
                                @endif
                            </div>
                            <p class="mb-1 text-secondary fs-6">
                                {{ $item['message'] }}
                            </p>
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><i class="fa-regular fa-clock me-1"></i>{{ $item['time_ago'] }}</span>
                                <span>&bull;</span>
                                <span class="text-capitalize">{{ $item['type_label'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ $item['action_url'] }}" class="btn btn-sm btn-dark fw-bold px-3">
                            {{ $item['action_label'] }} <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                        @if(!$item['is_read'])
                        <form action="{{ route('notifications.mark-read') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="id" value="{{ $item['id'] }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as Read">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="fa-regular fa-bell-slash fa-3x mb-3 text-muted opacity-50"></i>
            <h4 class="fw-bold text-dark mb-1">No Notifications Found</h4>
            <p class="text-muted fs-6 mb-3">There are no pending alerts or notifications for this filter.</p>
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-dark btn-sm fw-bold">
                {{ __('Reset Filter') }}
            </a>
        </div>
        @endif
    </div>
</div>

@endsection
