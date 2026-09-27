@extends('layouts.admin')

@section('title')
  {{ __('Performance Report') }}
@endsection

@section('header')
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">
      <i class="fas fa-chart-line me-2"></i>{{ __('Performance Report') }}
    </h1>
    <button type="button" class="btn btn-primary btn-sm" onclick="printReport()">
      <i class="fas fa-print"></i>
      <span class="ps-1">{{ __('Print Report') }}</span>
    </button>
  </div>
@endsection

@section('content')
  <section class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end" id="reportFilters">
            <div class="col-md-3">
              <label for="from" class="form-label">{{ __('From') }}</label>
              <input type="date" name="from" id="from" class="form-control" value="{{ $from->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
              <label for="to" class="form-label">{{ __('To') }}</label>
              <input type="date" name="to" id="to" class="form-control" value="{{ $to->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
              <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-1"></i>{{ __('Filter') }}
              </button>
            </div>
            <div class="col-md-2">
              <a href="{{ url()->current() }}" class="btn btn-secondary w-100">
                <i class="fas fa-rotate-left me-1"></i>{{ __('Reset') }}
              </a>
            </div>
            <div class="col-md-2">
              <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="toggleWeekly" checked>
                <label class="form-check-label" for="toggleWeekly">{{ __('Weekly View') }}</label>
              </div>
            </div>
          </form>
          <div class="mt-3 d-flex flex-wrap gap-3 text-muted">
            <span><i class="fas fa-calendar-day me-1"></i>{{ $from->format('d M Y') }} - {{ $to->format('d M Y') }}</span>
            <span><i class="fas fa-briefcase me-1"></i>{{ __('Working days') }}: <strong>{{ $workingDays }}</strong></span>
          </div>
        </div>
      </div>

      <div id="printReport">
        <div class="card">
          <div class="table-responsive">
            <table class="table table-hover align-middle my-0" id="performanceTable">
            <thead class="table-light text-uppercase small text-muted">
              <tr>
                <th style="width: 50px;">#</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Attendance Metrics') }}</th>
                <th class="text-end" style="width: 200px;">{{ __('Performance Score') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($employees as $row)
                <tr>
                  <td class="text-muted fw-bold">{{ $loop->iteration }}</td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar-mono flex-shrink-0" style="width:40px;height:40px;background:#09090b;color:#ffffff;font-size:.85rem;font-weight:700;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        {{ strtoupper(substr($row['employee']->firstname ?? 'E', 0, 1)) }}{{ strtoupper(substr($row['employee']->lastname ?? 'M', 0, 1)) }}
                      </div>
                      <div>
                        <a href="{{ route('admin.employee.show', $row['employee']->id ?? 1) }}" class="fw-bold fs-5 text-dark text-decoration-none text-hover-primary d-block">
                          {{ $row['employee']->firstname }} {{ $row['employee']->lastname }}
                        </a>
                        <div class="small text-muted">
                          <span>{{ $row['employee']->designation->title ?? 'Staff' }}</span> &bull; <span>{{ $row['employee']->department->title ?? 'General' }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                        <i class="fa-solid fa-check me-1"></i><strong>{{ $row['present'] }}</strong> Present
                      </span>
                      <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                        <i class="fa-solid fa-xmark me-1"></i><strong>{{ $row['absent'] }}</strong> Absent
                      </span>
                      @if($row['lateCount'] > 0)
                        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning px-2 py-1">
                          <i class="fa-solid fa-clock me-1 text-danger"></i><strong>{{ $row['lateCount'] }}</strong> Late
                        </span>
                      @endif
                      @if($row['overTime'] > 0)
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                          <i class="fa-regular fa-clock me-1"></i><strong>{{ $row['overTime'] }}</strong> OT
                        </span>
                      @endif
                    </div>
                  </td>
                  <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                      <div class="progress flex-grow-1" style="height: 10px; max-width: 110px;">
                        @php $barColor = $row['performance'] >= 80 ? 'bg-success' : ($row['performance'] >= 50 ? 'bg-warning' : 'bg-danger'); @endphp
                        <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ min($row['performance'], 100) }}%"></div>
                      </div>
                      <span class="badge {{ $barColor }} fs-6 fw-bold">{{ $row['performance'] }}%</span>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">{{ __('No employees found.') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </div>
  </section>
@endsection

@section('script')
  <script>
    function printReport() {
      const tableEl = document.getElementById('performanceTable');
      if (!tableEl) return;

      const table = window.$.fn.DataTable ? $('#performanceTable').DataTable() : null;
      const hadPagination = table && typeof table.page === 'function';
      const previousLength = hadPagination ? table.page.len() : null;
      if (hadPagination && previousLength !== -1) {
        table.page.len(-1).draw(false);
      }

      const source = document.getElementById('printReport').cloneNode(true);
      const strips = source.querySelectorAll('.btn, .dataTables_filter, .dataTables_length, .dataTables_info, .dataTables_paginate, .progress, .card-header');
      strips.forEach(function(el) { el.remove(); });

      const printWindow = window.open('', '_blank', 'width=1100,height=800');
      printWindow.document.write(`
        <html>
          <head>
            <title>Performance Report</title>
            <style>
              body { font-family: 'Segoe UI', Arial, sans-serif; margin: 24px; color: #212529; }
              h2 { margin: 0 0 4px; }
              .meta { margin: 0 0 16px; color: #555; font-size: 13px; }
              table { width: 100%; border-collapse: collapse; font-size: 13px; }
              table th, table td { border: 1px solid #dee2e6; padding: 8px 10px; text-align: left; }
              table th { background: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
              tr { display: table-row !important; }
              td, th { display: table-cell !important; }
              .text-success { color: #198754 !important; }
              .text-danger { color: #dc3545 !important; }
              .text-warning { color: #ad8c00 !important; }
              @media print { body { margin: 0; } }
            </style>
          </head>
          <body>
            <h2>Performance Report</h2>
            <p class="meta">${@json($from->format('d M Y'))} - ${@json($to->format('d M Y'))} &middot; Working days: ${@json($workingDays)} &middot; Generated: ${@json(now()->format('d M Y h:i A'))}</p>
            <div id="printReport">${source.innerHTML}</div>
            <script>window.onload = function() { window.print(); };<\/script>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();

      if (hadPagination && previousLength !== null && previousLength !== -1) {
        table.page.len(previousLength).draw(false);
      }
    }
  </script>
  <script>
    $(document).ready(function() {
      const from = $('#from');
      const to = $('#to');

      from.on('change', function() {
        to.attr('min', from.val());
      });
      to.on('change', function() {
        if (from.val() && to.val() < from.val()) {
          from.val(to.val());
        }
      });

      $('#toggleWeekly').on('change', function() {
        if ($(this).is(':checked')) {
          $('#performanceTable').DataTable().destroy();
        } else {
          $('#performanceTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            info: false
          });
        }
      });
    });
  </script>
@endsection