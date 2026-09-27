<nav class="navbar navbar-expand navbar-light navbar-bg">
  <a class="sidebar-toggle js-sidebar-toggle">
    <i class="hamburger align-self-center"></i>
  </a>

  <div class="d-none d-lg-flex align-items-center text-muted me-3">
    <i class="fa-regular fa-calendar me-2"></i>
    <span id="navbarLiveDate" class="me-3">{{ now()->format('M d, Y') }}</span>
    <i class="fa-regular fa-clock me-2"></i>
    <span id="navbarLiveTime">{{ now()->format('g:i A') }}</span>
  </div>

  <ul class="navbar-nav navbar-align">
    <li class="nav-item dropdown">
      <a class="nav-icon nav-link dropdown-toggle" href="javascript:void(0)" id="itemsDropdown" data-bs-toggle="dropdown">
        <i class="align-middle" data-feather="plus"></i>
        <span class="align-middle" style="font-size: 0.85rem;">New Items</span>
      </a>
      <div class="dropdown-menu py-0" aria-labelledby="itemsDropdown">
        <div class="dropdown-menu-header">{{ __('Add New Opion') }}</div>
        <div class="list-group">
          @php $roleSlug = Auth::check() ? Auth::user()->role->slug : ''; @endphp
          <a href="{{ $roleSlug === 'super-admin' ? route('department.create') : ($roleSlug === 'administrator' ? route('admin.department.create') : route('hr.department.create')) }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('Department') }}</span>
          </a>
          <a href="{{ $roleSlug === 'super-admin' ? route('designation.create') : ($roleSlug === 'administrator' ? route('admin.designation.create') : route('hr.designation.create')) }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('Designation') }}</span>
          </a>
          <a href="{{ $roleSlug === 'super-admin' ? route('employee.create') : ($roleSlug === 'administrator' ? route('admin.employee.create') : route('hr.employee.create')) }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('Employee') }}</span>
          </a>
          <a href="{{ $roleSlug === 'super-admin' ? route('attendance.create') : ($roleSlug === 'administrator' ? route('admin.attendance.create') : ($roleSlug === 'hr-manager' ? route('hr.attendance.index') : route('moderator.attendance.index'))) }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('Attendance') }}</span>
          </a>
          <a href="{{ $roleSlug === 'super-admin' ? route('leaves.create') : ($roleSlug === 'administrator' ? route('admin.leaves.create') : route('hr.leaves.create')) }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('Leave') }}</span>
          </a>
          <a href="{{ $roleSlug === 'super-admin' ? route('payroll.create') : ($roleSlug === 'administrator' ? route('admin.payroll.create') : ($roleSlug === 'hr-manager' ? route('hr.payroll.create') : route('manager.payroll.create'))) }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('Payroll') }}</span>
          </a>
          @if ($roleSlug === 'super-admin' || $roleSlug === 'administrator')
          <a href="{{ $roleSlug === 'super-admin' ? route('user.create') : route('admin.users.create') }}" class="list-group-item">
            <i class="fas fa-plus align-middle"></i>
            <span class="text-dark ps-2">{{ __('User') }}</span>
          </a>
          @endif
        </div>
      </div>
    </li>
  </ul>

  <div class="navbar-collapse collapse">
    <ul class="navbar-nav navbar-align">
      <li class="nav-item dropdown">
        <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="position-relative">
            <i class="align-middle" data-feather="bell"></i>
            @if(isset($navbarUnreadCount) && $navbarUnreadCount > 0)
              <span class="indicator bg-danger">{{ $navbarUnreadCount }}</span>
            @endif
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0 shadow border" aria-labelledby="alertsDropdown" style="min-width: 320px; max-width: 380px;">
          <div class="dropdown-menu-header d-flex justify-content-between align-items-center py-2 px-3 bg-dark text-white">
            <span class="fw-bold">{{ isset($navbarUnreadCount) && $navbarUnreadCount > 0 ? $navbarUnreadCount . ' New Notifications' : 'Notifications' }}</span>
            @if(isset($navbarUnreadCount) && $navbarUnreadCount > 0)
              <form action="{{ route('notifications.clear-all') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-white text-decoration-none p-0" style="font-size: 0.75rem;" title="Mark all as read">
                  <i class="fa-solid fa-check-double me-1"></i>Mark Read
                </button>
              </form>
            @endif
          </div>
          <div class="list-group list-group-flush" style="max-height: 360px; overflow-y: auto;">
            @forelse($navbarNotifications ?? [] as $item)
              <a href="{{ $item['action_url'] }}" class="list-group-item list-group-item-action py-3 px-3 {{ !$item['is_read'] ? 'bg-light' : '' }}">
                <div class="d-flex align-items-start gap-2">
                  <div class="flex-shrink-0 mt-1">
                    <span class="badge rounded-circle p-2 bg-{{ $item['color'] }} bg-opacity-10 text-{{ $item['color'] }}">
                      <i class="{{ $item['icon'] }}" style="font-size: 0.85rem;"></i>
                    </span>
                  </div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="fw-bold text-dark small">{{ $item['title'] }}</div>
                      <span class="badge bg-{{ $item['color'] }} bg-opacity-10 text-{{ $item['color'] }} border border-{{ $item['color'] }} py-0 px-1" style="font-size: 0.65rem;">
                        {{ $item['badge'] }}
                      </span>
                    </div>
                    <div class="text-secondary small mt-1" style="font-size: 0.8rem; line-height: 1.3;">
                      {{ $item['message'] }}
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                      <i class="fa-regular fa-clock me-1"></i>{{ $item['time_ago'] }}
                    </div>
                  </div>
                </div>
              </a>
            @empty
              <div class="p-4 text-center text-muted">
                <i class="fa-regular fa-bell-slash fa-2x mb-2 opacity-50"></i>
                <div class="small fw-semibold">No new notifications</div>
                <div class="small text-muted" style="font-size: 0.75rem;">You are completely caught up!</div>
              </div>
            @endforelse
          </div>
          <div class="dropdown-menu-footer text-center py-2 bg-light border-top">
            <a href="{{ route('notifications.index') }}" class="text-primary fw-bold small text-decoration-none">
              <i class="fa-solid fa-list me-1"></i>{{ __('View Notification Center') }} &rarr;
            </a>
          </div>
        </div>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown" data-bs-toggle="dropdown">
          <div class="position-relative">
            <i class="align-middle" data-feather="message-square"></i>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="messagesDropdown">
          <div class="dropdown-menu-header">
            <div class="position-relative">4 New Messages</div>
          </div>
          <div class="list-group">
            <a href="#" class="list-group-item">
              <div class="row g-0 align-items-center">
                <div class="col-2">
                  <img src="{{ asset('img/avatars/avatar-5.jpg')}}" class="avatar img-fluid rounded-circle" alt="Vanessa Tucker" />
                </div>
                <div class="col-10 ps-2">
                  <div class="text-dark">Vanessa Tucker</div>
                  <div class="text-muted small mt-1">Nam pretium turpis et arcu. Duis arcu tortor.</div>
                  <div class="text-muted small mt-1">15m ago</div>
                </div>
              </div>
            </a>
            <a href="#" class="list-group-item">
              <div class="row g-0 align-items-center">
                <div class="col-2">
                  <img src="{{ asset('img/avatars/avatar-2.jpg')}}" class="avatar img-fluid rounded-circle" alt="William Harris" />
                </div>
                <div class="col-10 ps-2">
                  <div class="text-dark">William Harris</div>
                  <div class="text-muted small mt-1">Curabitur ligula sapien euismod vitae.</div>
                  <div class="text-muted small mt-1">2h ago</div>
                </div>
              </div>
            </a>
            <a href="#" class="list-group-item">
              <div class="row g-0 align-items-center">
                <div class="col-2">
                  <img src="{{ asset('img/avatars/avatar-4.jpg')}}" class="avatar img-fluid rounded-circle" alt="Christina Mason" />
                </div>
                <div class="col-10 ps-2">
                  <div class="text-dark">Christina Mason</div>
                  <div class="text-muted small mt-1">Pellentesque auctor neque nec urna.</div>
                  <div class="text-muted small mt-1">4h ago</div>
                </div>
              </div>
            </a>
            <a href="#" class="list-group-item">
              <div class="row g-0 align-items-center">
                <div class="col-2">
                  <img src="{{ asset('img/avatars/avatar-3.jpg')}}" class="avatar img-fluid rounded-circle" alt="Sharon Lessman" />
                </div>
                <div class="col-10 ps-2">
                  <div class="text-dark">Sharon Lessman</div>
                  <div class="text-muted small mt-1">Aenean tellus metus, bibendum sed, posuere ac, mattis non.</div>
                  <div class="text-muted small mt-1">5h ago</div>
                </div>
              </div>
            </a>
          </div>
          <div class="dropdown-menu-footer">
            <a href="#" class="text-muted">Show all messages</a>
          </div>
        </div>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
          <i class="align-middle" data-feather="settings"></i>
        </a>
        <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
          <img src="{{ asset('img/avatars/dummy.png') }}" class="avatar img-fluid rounded me-1" alt="Charles Hall" /> <span class="text-dark">{{ Auth::user()->name }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-end">
          <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="align-middle me-1" data-feather="user"></i> {{ __('Profile') }}</a>
          <a class="dropdown-item" href="{{ route('payroll.transactions') }}"><i class="align-middle me-1" data-feather="dollar-sign"></i> {{ __('Transactions') }}</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="align-middle me-1" data-feather="settings"></i> {{ __('Account Settings') }}</a>
          <a class="dropdown-item" href="javascript:void(0)"><i class="align-middle me-1" data-feather="help-circle"></i> {{ __('Help Center') }}</a>
          <div class="dropdown-divider"></div>
          {{-- <a class="dropdown-item" href="javascript:void(0)">Log out</a> --}}
          <!-- Authentication -->
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a class="dropdown-item" href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
              <i class="align-middle me-1" data-feather="log-out"></i>
              <span class="me-1">{{ __('Log Out') }}</span>
            </a>
          </form>
        </div>
      </li>
    </ul>
  </div>
</nav>