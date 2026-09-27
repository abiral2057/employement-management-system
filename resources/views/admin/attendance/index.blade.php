@extends('layouts.admin')

@section('title')
    {{ __('Manage attendance') }}
@endsection

@section('header')
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
      <h1 class="display-6 fw-extrabold text-dark tracking-tight mb-1">{{ __('Daily Attendance Sheet') }}</h1>
      <p class="text-secondary small mb-0">{{ __('Live month-to-date staff attendance tracking, check-in verification, and departure registry') }}</p>
    </div>
    <div class="d-flex gap-2">
      <span class="badge bg-dark text-white px-3 py-2 rounded-pill fs-6">
        <i class="fa-solid fa-calendar-day text-yellowgreen me-1"></i> {{ now()->format('F Y') }}
      </span>
    </div>
  </div>
@endsection

@section('content')
<section class="row">
  <div class="col-12">
    <div class="card flex-fill">
      <div class="card-header">              
        <h5 class="card-title mb-0">{{ __('Attendace of the Month August') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover my-0 table-bordered">
       
          <thead>
            <tr>
                <th scope="col" style="min-width: 220px;">{{ __('Employee') }}</th>
                @php
                    $today = today();
                    $dates = [];
                    for ($i = 1; $i < $today->daysInMonth + 1; ++$i) {
                        $dates[] = $i;
                    }
                @endphp
                @foreach ($dates as $date)
                    <th scope="col" class="text-center" style="min-width: 40px;">
                        {{ $date }}
                    </th>
                @endforeach
            </tr>
        </thead>
  
        <tbody>
  
  
            <form action="{{ Auth::user()->role->slug === 'super-admin' ? route('check.store') : (Auth::user()->role->slug === 'administrator' ? route('admin.check.store') : (Auth::user()->role->slug === 'hr-manager' ? route('hr.check.store') : route('moderator.check.store')) ) }}" method="post">
               
                <button type="submit" class="btn btn-success fw-bold" style="display: flex; margin:10px"><i class="fa-solid fa-floppy-disk me-1"></i> Save Attendance</button>
                @csrf
                @foreach ($employees as $employee)
  
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
  
                    <tr>
                        <td class="py-2">
                            <a href="{{ route('admin.employee.show', $employee->id) }}" class="fw-bold fs-6 text-dark text-decoration-none text-hover-primary d-block">
                                {{ $employee->firstname }} {{ $employee->lastname }}
                            </a>
                            <div class="small text-muted">
                                <span class="badge bg-light text-dark border me-1">{{ $employee->unique_id ?? ('#' . $employee->id) }}</span>
                                <span>{{ optional($employee->designation)->title ?? 'Staff' }}</span>
                            </div>
                        </td>
  
  
  
  
  
  
                        @for ($i = 1; $i < $today->daysInMonth + 1; ++$i)
  
  
                            @php
                                
                                $date_picker = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->format('Y-m-d');

                                $isFriday = \Carbon\Carbon::createFromDate($today->year, $today->month, $i)->isFriday();
                                
                                $check_attd = \App\Models\Attendance::query()
                                    ->where('employee_id', $employee->id)
                                    ->where('attendance_date', $date_picker)
                                    // ->where('status', $date_picker)
                                    ->first();
                                
                                $check_depart = \App\Models\Depart::query()
                                    ->where('employee_id', $employee->id)
                                    ->where('depart_date', $date_picker)
                                    // ->where('status', $date_picker)
                                    ->first();
                                
                            @endphp
                            <td class="@if ($isFriday) bg-light @endif">
  
                                {{-- <div class="form-check form-check-inline">
                                    <input class="form-check-input checkbox" id="check_box"
                                        name="attd[{{ $date_picker }}][{{ $employee->id }}]" type="checkbox"
                                        @if (isset($check_attd))  checked @endif id="inlineCheckbox1" value="1">
  
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input " id="check_box"
                                        name="depart[{{ $date_picker }}][{{ $employee->id }}]]" type="checkbox"
                                        @if (isset($check_depart))  checked @endif id="inlineCheckbox2" value="1">
  
                                </div> --}}

                                {{-- <div class="form-check form-check-inline">
                                    <input class="form-check-input checkbox" id="check_box"
                                        name="attd[{{ $date_picker }}][{{ $employee->id }}]" type="checkbox"
                                       @if (isset($check_attd)) checked @endif id="inlineCheckbox1" >
                                       
  
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input checkbox" id="check_box"
                                        name="depart[{{ $date_picker }}][{{ $employee->id }}]]" type="checkbox"
                                       @if (isset($check_depart)) checked @endif id="inlineCheckbox2" >
  
                                </div> --}}

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input checkbox" id="check_box_attd_{{ $i }}"
                                        name="attd[{{ $date_picker }}][{{ $employee->id }}]" type="checkbox"
                                        @if (isset($check_attd)) checked @endif >
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input checkbox" id="check_box_depart_{{ $i }}"
                                        name="depart[{{ $date_picker }}][{{ $employee->id }}]" type="checkbox"
                                        @if (isset($check_depart)) checked @endif >
                                </div>
  
                            </td>
  
                        @endfor
                        
                    </tr>
                @endforeach
  
            </form>
  
  
        </tbody>
  
        </table>
      </div>
      
    </div>
  </div>
</section>
@endsection

@section('script')
{{-- <input type="checkbox" name="status" id="statusCheckbox">
<label for="statusCheckbox">Status</label> --}}

<script>
document.addEventListener("DOMContentLoaded", function() {
    // const checkbox = document.getElementById("statusCheckbox");
    const checkbox = document.querySelector(".checkbox");
    checkbox.addEventListener("change", function() {
        if (checkbox.checked) {
            checkbox.value = "1"; // Checked, value is 1
        } else {
            checkbox.value = "0"; // Unchecked, value is 0
        }
    });
});
</script>

@endsection