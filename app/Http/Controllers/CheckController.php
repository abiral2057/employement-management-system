<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Check;
use App\Models\Depart;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    public function CheckStore(Request $request)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            if (isset($request->attd)) {
                foreach ($request->attd as $dateKey => $empValues) {
                    foreach ($empValues as $empId => $value) {
                        $employee = Employee::with('schedule')->find($empId);
                        if ($employee) {
                            $isFriday = Carbon::createFromFormat('Y-m-d', $dateKey)->isFriday();
                            $checkboxValue = $request->has("attd.$dateKey.$empId") ? 1 : 0;

                            $existing = Attendance::where('attendance_date', $dateKey)
                                ->where('employee_id', $empId)
                                ->where('type', 0)
                                ->first();

                            if (!$existing) {
                                $data = new Attendance();
                                $data->employee_id = $empId;
                                $timeIn = optional($employee->schedule)->time_in ?? '09:00:00';
                                $data->attendance_time = date('H:i:s', strtotime($timeIn));
                                $data->attendance_date = $dateKey;
                                $data->type = 0;
                                $data->status = $isFriday && $checkboxValue === 0 ? null : $checkboxValue;
                                $data->save();
                            }
                        }
                    }
                }
            }

            if (isset($request->depart)) {
                foreach ($request->depart as $dateKey => $empValues) {
                    foreach ($empValues as $empId => $value) {
                        $employee = Employee::with('schedule')->find($empId);
                        if ($employee) {
                            $existing = Depart::where('depart_date', $dateKey)
                                ->where('employee_id', $empId)
                                ->where('type', 1)
                                ->first();

                            if (!$existing) {
                                $data = new Depart();
                                $data->employee_id = $empId;
                                $timeOut = optional($employee->schedule)->time_out ?? '17:00:00';
                                $data->depart_time = $timeOut;
                                $data->depart_date = $dateKey;
                                $data->type = 1;
                                $data->status = 1;
                                $data->save();
                            }
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Attendance records updated successfully!');
    }

    public function sheetReport()
    {
        $employees = Employee::with(['department', 'designation', 'schedule'])->where('status', 1)->get();
        return view('admin.attendance.report', compact('employees'));
    }
}
