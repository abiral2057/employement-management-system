<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LateTime;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class LateTimeController extends Controller
{
    /**
     * Display a listing of late time records.
     */
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', today()->format('Y-m'));
        $employeeFilter = $request->input('employee_id');

        $query = LateTime::with(['employee.department', 'employee.designation', 'employee.schedule'])
            ->orderBy('latetime_date', 'desc');

        if ($request->filled('month')) {
            $carbonMonth = Carbon::parse($selectedMonth . '-01');
            $query->whereBetween('latetime_date', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString()
            ]);
        }

        if ($employeeFilter) {
            $query->where('employee_id', $employeeFilter);
        }

        $lateTimes = $query->paginate(25)->withQueryString();

        $employees = Employee::where('status', 1)->orderBy('firstname')->get();

        return view('admin.attendance.latetime', compact('lateTimes', 'employees', 'selectedMonth', 'employeeFilter'));
    }

    /**
     * Remove late time record.
     */
    public function destroy($id)
    {
        $late = LateTime::findOrFail($id);
        $late->delete();

        return back()->with('success', 'Late record deleted.');
    }

    /**
     * Auto calculate late time from clock-in.
     */
    public static function lateTime(Employee $employee)
    {
        $schedule = $employee->schedule;
        if (!$schedule || !$schedule->time_in) {
            return;
        }

        $current_t = new DateTime(date('H:i:s'));
        $scheduled_in = new DateTime($schedule->time_in);

        if ($current_t > $scheduled_in) {
            $difference = $scheduled_in->diff($current_t)->format('%H:%I:%S');

            $latetime = new LateTime();
            $latetime->employee_id = $employee->id;
            $latetime->latetime_duration = $difference;
            $latetime->latetime_date = date('Y-m-d');
            $latetime->save();
        }
    }
}
