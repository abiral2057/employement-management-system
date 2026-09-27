@extends('layouts.admin')

@section('title')
  {{ __('Manage payroll') }}
@endsection

@section('header')
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-3">Payroll</h1>
    <a href="" class="btn btn-primary">
      <i class="fas fa-plus"></i>
      <span class="ps-1">{{ __('Add New') }}</span>
    </a>
  </div>
@endsection

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card flex-fill">
      <div class="card-header">              
        <h5 class="card-title mb-0">{{ __('Employees Daily Attendance') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle my-0 data-table">   
          <thead class="table-light text-uppercase small text-muted">
            <tr>
                <th scope="col" style="width: 50px;">#</th>
                <th scope="col">{{ __('Employee') }}</th>
                <th scope="col">{{ __('Basic & Allowances') }}</th>
                <th scope="col" class="text-end" style="width: 180px;">{{ __('Gross Salary') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $index => $employee)
              <tr class="employee">
                 <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                 <td>
                   <div class="d-flex align-items-center gap-3">
                     <div class="avatar-mono flex-shrink-0" style="width:40px;height:40px;background:#09090b;color:#ffffff;font-size:.85rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                       {{ strtoupper(substr($employee->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($employee->lastname ?? 'M', 0, 1)) }}
                     </div>
                     <div>
                       <a href="{{ route('admin.employee.show', $employee->id ?? 1) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                         {{ $employee->firstname }} {{ $employee->lastname }}
                       </a>
                       <div class="small text-muted">
                         <span class="badge bg-light text-dark border me-1">{{ $employee->unique_id ?? '—' }}</span>
                         <span>{{ optional($employee->department)->title ?? 'General' }}</span>
                       </div>
                     </div>
                   </div>
                 </td>
                 <td>
                   <div class="d-flex align-items-center gap-2 flex-wrap">
                     <span class="fw-bold fs-6 text-primary">€ {{ number_format($employee->salary['basic'] ?? 0, 2) }}</span>
                     <span class="badge bg-light text-dark border">Basic</span>
                     @if(isset($employee->salary['house_rent']) && $employee->salary['house_rent'] > 0)
                       <span class="badge bg-light text-dark border">+ Rent: €{{ number_format($employee->salary['house_rent'], 2) }}</span>
                     @endif
                   </div>
                 </td>
                 <td class="text-end">
                    <span class="fw-bold fs-4 text-success">€ {{ number_format($employee['gross_salary'] ?? 0, 2) }}</span>
                 </td>
              </tr>
            @endforeach
        </tbody>
        </table>
      </div>
      
    </div>
  </div>
</section>
@endsection

@section('script')
  <script>
        $(document).ready(function() {
            $(".employee").each(function() {
                var $employee = $(this);
                var basic = parseFloat($employee.find(".basic").val());
                var rent = parseFloat($employee.find(".rent").val());
                var grossSalary = basic + rent;
                
                $employee.find(".result").val(grossSalary.toFixed(2));

                $gross = localStorage.setItem("grossSalary{{ $index }}", grossSalary.toFixed(2));
            });
        });
  </script>
@endsection