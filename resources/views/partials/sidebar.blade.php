<aside id="sidebar" class="sidebar js-sidebar">
  <div class="sidebar-content js-simplebar">
    <a class="sidebar-brand d-flex align-items-center" href="{{ url('/') }}">
      <span class="brand-graphic" style="width: 32px; height: 32px; background: #ffffff; color: #09090b; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
      </span>
      <span class="align-middle fw-bold text-white ms-2" style="font-size: 1.1rem; letter-spacing: -0.02em;">HRMS <span style="font-size: 0.65rem; font-weight: 600; padding: 2px 6px; background: #27272a; color: #d4d4d8; border-radius: 4px; vertical-align: middle; margin-left: 4px;">PRO</span></span>
    </a>

    <ul class="sidebar-nav">
      <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('super.dashboard') : 
        ( Auth::user()->role->slug === 'administrator' ? route('admin.dashboard')  : 
        ( Auth::user()->role->slug === 'moderator' ? route('moderator.dashboard')  : 
        ( (Auth::user()->role->slug === 'hr-manager' || Auth::user()->role->slug === 'hr') ? route('hr.dashboard')  : route('payroll.dashboard')))) }}">
          <i class="align-middle" data-feather="sliders"></i>
          <span class="align-middle">{{ __('Dashboard') }}</span>
        </a>
      </li>
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager'))
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('super.performance.report') : (Auth::user()->role->slug === 'administrator' ? route('admin.performance.report') : route('hr.performance.report') ) }}">
            <i class="fa-solid fa-chart-line"></i>
            <span class="align-middle">{{ __('Performance Report') }}</span>
          </a>
        </li>
      @endif
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager' || Auth::user()->role->slug === 'hr' || Auth::user()->role->slug === 'payroll' || Auth::user()->role->slug === 'payroll-manager'))
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('notifications.index') }}">
            <i class="fa-solid fa-bell"></i>
            <span class="align-middle">{{ __('Notifications') }}</span>
          </a>
        </li>
      @endif
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator'))
        <li class="sidebar-header">{{ __('Users Management') }}</li>
      {{-- @endif
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator')) --}}
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('user.index') : route('admin.users.index') }}">
          <i class="fas fa-user align-middle"></i>
          <span class="align-middle">{{ __('Manage Users') }}</span>
        </a>
        </li>
      @endif
      
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin'))
       <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('roles.index') }}">
            <i class="fas fa-user-shield align-middle"></i> <span class="align-middle">{{ __('User Settings') }}</span>
          </a>
        </li> 
      @endif
        
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager'))
        <li class="sidebar-header">{{ __('Employee Management') }}</li>
      {{-- @endif

      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager')) --}}
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.index') : route('hr.employee.index') ) }}">
          <i class="fa-solid fa-users-viewfinder"></i>
          <span class="align-middle">{{ __('Manage Employees') }}</span>
        </a>
        </li>
      {{-- @endif

      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager')) --}}
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('department.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.department.index') : route('hr.department.index') ) }}">
          <i class="fa-solid fa-users-gear"></i>
          <span class="align-middle">{{ __('Manage Departments') }}</span>
        </a>
        </li>
      {{-- @endif
      
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager')) --}}
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('designation.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.designation.index') : route('hr.designation.index') ) }}">
          <i class="fa-solid fa-file-lines"></i>
          <span class="align-middle">{{ __('Manage Designations') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('employee.search') : (Auth::user()->role->slug === 'administrator' ? route('admin.employee.search') : route('hr.employee.search') ) }}">
          <i class="fa-solid fa-magnifying-glass-chart"></i>
          <span class="align-middle">{{ __('Employee Search') }}</span>
        </a>
        </li>
      @endif

      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'moderator' || Auth::user()->role->slug === 'hr-manager'))
        <li class="sidebar-header">{{ __('Attendance & Shifts') }}</li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ route('shifts.roster') }}">
          <i class="fa-solid fa-business-time"></i>
          <span class="align-middle">{{ __('Shift Roster (Day/Night)') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('schedule.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.schedule.index') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.schedule.index') : route('moderator.schedule.index') )) }}">
          <i class="fa-solid fa-clock"></i>
          <span class="align-middle">{{ __('Shift Schedules') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('attendance.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.attendance.index') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.attendance.index') : route('moderator.attendance.index') )) }}">
          <i class="fa-solid fa-calendar-days"></i>
          <span class="align-middle">{{ __('Daily Attendance') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ route('attendance.overtime') }}">
          <i class="fa-solid fa-stopwatch"></i>
          <span class="align-middle">{{ __('Over Time (OT)') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ route('attendance.latetime') }}">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <span class="align-middle">{{ __('Late Time') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('sheet.report') : (Auth::user()->role->slug === 'administrator' ? route('admin.sheet.report') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.sheet.report') : route('moderator.sheet.report') )) }}">
          <i class="fa-solid fa-book"></i>
          <span class="align-middle">{{ __('Sheet Report') }}</span>
        </a>
        </li>
      @endif
      
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'hr-manager'))
        <li class="sidebar-header">{{ __('Leave Management') }}</li>  
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('leaves.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.leaves.index') : route('hr.leaves.index') ) }}">
          <i class="fa-solid fa-person-walking-arrow-right"></i>
          <span class="align-middle">{{ __('Manage Leaves') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('leaves.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.leaves.create') : route('hr.leaves.create') ) }}">
          <i class="fa-solid fa-file-pen"></i>
          <span class="align-middle">{{ __('Apply For Leave') }}</span>
        </a>
        </li>
      @endif
          
      @if (Auth::check() && (Auth::user()->role->slug === 'super-admin' || Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'payroll-manager' || Auth::user()->role->slug === 'hr-manager'))
        <li class="sidebar-header">{{ __('Payroll System') }}</li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ route('payroll.due') }}">
          <i class="fa-solid fa-file-invoice-dollar text-warning"></i>
          <span class="align-middle fw-semibold text-warning">{{ __('Payment Due & Reminders') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('payroll.index') : (Auth::user()->role->slug === 'administrator' ? route('admin.payroll.index') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.payroll.index') : route('manager.payroll.index') )) }}">
          <i class="fa-solid fa-file"></i>
          <span class="align-middle">{{ __('Manage Payroll') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ Auth::user()->role->slug === 'super-admin' ? route('payroll.create') : (Auth::user()->role->slug === 'administrator' ? route('admin.payroll.create') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.payroll.create') : route('manager.payroll.create') )) }}">
          <i class="fa-solid fa-calculator"></i>
          <span class="align-middle">{{ __('Generate Payroll') }}</span>
        </a>
        </li>
        <li class="sidebar-item">
        <a class="sidebar-link" href="{{ route('payroll.report') }}">
          <i class="fa-solid fa-file-export"></i>
          <span class="align-middle">{{ __('Payroll Sheet') }}</span>
        </a>
        </li>
        <li class="sidebar-item {{ request()->routeIs('payroll.transactions*') ? 'active' : '' }}">
        <a class="sidebar-link" href="{{ route('payroll.transactions') }}">
          <i class="fa-solid fa-receipt"></i>
          <span class="align-middle">{{ __('Transaction History') }}</span>
        </a>
        </li>
      @endif
      
      
      {{-- <li class="sidebar-item">
        <a class="sidebar-link" href="javascript:void(0)">
          <i class="fa-solid fa-file-export"></i>
          <span class="align-middle">{{ __('Generate Payroll') }}</span>
        </a>
      </li> --}}
      
    </ul>
  </div>
</aside>