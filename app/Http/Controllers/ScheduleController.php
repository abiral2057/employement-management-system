<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = Schedule::withCount('employees')->get();
        return view('admin.schedule.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.schedule.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:100|unique:schedules,title',
            'shift_type' => 'nullable|string|in:day,night,extra',
            'time_in' => 'required',
            'time_out' => 'required',
            'cross_midnight' => 'nullable|boolean',
            'rate_multiplier' => 'nullable|numeric|min:0.5',
        ]);

        $data['shift_type'] = $data['shift_type'] ?? 'day';
        // Auto detect cross-midnight
        if ($data['time_out'] < $data['time_in']) {
            $data['cross_midnight'] = true;
            if ($data['shift_type'] === 'day') {
                $data['shift_type'] = 'night';
            }
        } else {
            $data['cross_midnight'] = (bool) ($request->cross_midnight ?? false);
        }

        Schedule::create($data);
        return redirect()->route('schedule.index')->with('success', 'Schedule created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        return view('admin.schedule.edit', compact('schedule'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = $id instanceof Schedule ? $id : Schedule::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:100|unique:schedules,title,' . $schedule->id,
            'shift_type' => 'nullable|string|in:day,night,extra',
            'time_in' => 'required',
            'time_out' => 'required',
            'cross_midnight' => 'nullable|boolean',
            'rate_multiplier' => 'nullable|numeric|min:0.5',
        ]);

        $data['shift_type'] = $data['shift_type'] ?? 'day';
        if ($data['time_out'] < $data['time_in']) {
            $data['cross_midnight'] = true;
            if ($data['shift_type'] === 'day') {
                $data['shift_type'] = 'night';
            }
        } else {
            $data['cross_midnight'] = (bool) ($request->cross_midnight ?? false);
        }

        $schedule->update($data);
        return redirect()->route('schedule.index')->with('success', 'Schedule updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $schedule = $id instanceof Schedule ? $id : Schedule::findOrFail($id);

        // Check if employees are assigned
        if ($schedule->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete schedule because ' . $schedule->employees()->count() . ' employees are currently assigned to it.');
        }

        $schedule->delete();
        return back()->with('success', 'Schedule deleted successfully');
    }
}
