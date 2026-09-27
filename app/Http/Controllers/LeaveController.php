<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\EmployeeLeaveBalance;
use App\Models\Leave;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->input('status');
        $query = Leave::with(['employee.department', 'employee.designation'])->latest();

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('status', (int) $statusFilter);
        }

        $leaves = $query->get();
        $pendingCount = Leave::where('status', 0)->count();
        $approvedCount = Leave::where('status', 1)->count();
        $rejectedCount = Leave::where('status', 2)->count();

        $leaveTypes = [];
        try {
            $leaveTypes = LeaveType::where('status', true)->get()->keyBy('id');
        } catch (\Throwable $e) {}

        return view('admin.leave.index', compact('leaves', 'pendingCount', 'approvedCount', 'rejectedCount', 'leaveTypes', 'statusFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::where('status', 1)->get();
        $leaveTypes = [];
        try {
            $leaveTypes = LeaveType::where('status', true)->get();
        } catch (\Throwable $e) {}

        return view('admin.leave.create', compact('employees', 'leaveTypes'));
    }

    /**
     * Store a newly created resource in storage with validation & quota checks.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_type' => 'required|integer',
            'leave_reason' => 'nullable|string',
            'status' => 'nullable|integer',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $durationDays = $start->diffInDays($end) + 1;

        $employee = Employee::findOrFail($request->employee_id);
        $year = (int) $start->format('Y');

        // Check quota if employee_leave_balances is initialized
        try {
            $balance = EmployeeLeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $request->leave_type)
                ->where('year', $year)
                ->first();

            if ($balance && $durationDays > $balance->remaining_days) {
                return back()->withInput()->with('error', "Cannot request {$durationDays} day(s). The remaining available balance for this leave type is {$balance->remaining_days} day(s).");
            }

            if ($balance) {
                $balance->increment('pending_days', $durationDays);
            }
        } catch (\Throwable $e) {}

        $status = $request->input('status', 0);
        $data['status'] = $status;
        $leave = Leave::create($data);

        EmployeeHistory::log(
            $employee->id,
            'status_change',
            'Leave Request Submitted',
            "Submitted leave request for {$durationDays} day(s) ({$start->format('M d, Y')} to {$end->format('M d, Y')}). Reason: {$request->title}."
        );

        return redirect()->route(auth()->user()->role->slug === 'super-admin' ? 'leaves.index' : 'admin.leaves.index')
            ->with('success', "Leave request of {$durationDays} day(s) recorded successfully.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        $employees = Employee::where('status', 1)->get();
        $leaveTypes = [];
        try {
            $leaveTypes = LeaveType::where('status', true)->get();
        } catch (\Throwable $e) {}

        return view('admin.leave.edit', compact('leave', 'employees', 'leaveTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $oldStatus = $leave->status;
        $leave->update($request->all());

        if (isset($request->status) && $oldStatus != $request->status) {
            $statusLabel = $request->status == 1 ? 'Approved' : ($request->status == 2 ? 'Rejected' : 'Pending');
            EmployeeHistory::log(
                $leave->employee_id,
                'status_change',
                "Leave {$statusLabel}",
                "Leave request \"{$leave->title}\" was {$statusLabel} by " . (auth()->user()->name ?? 'Admin') . "."
            );
        }

        return back()->with('success', 'Leave record updated successfully.');
    }

    /**
     * Fast 1-click Approval.
     */
    public function approve($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->status = 1;
        $leave->save();

        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $durationDays = $start->diffInDays($end) + 1;
        $year = (int) $start->format('Y');

        try {
            $balance = EmployeeLeaveBalance::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type)
                ->where('year', $year)
                ->first();

            if ($balance) {
                $balance->decrement('pending_days', min($balance->pending_days, $durationDays));
                $balance->increment('used_days', $durationDays);
            }
        } catch (\Throwable $e) {}

        EmployeeHistory::log(
            $leave->employee_id,
            'status_change',
            'Leave Approved',
            "Approved leave request \"{$leave->title}\" ({$durationDays} days)."
        );

        return back()->with('success', "Leave request approved successfully.");
    }

    /**
     * Fast 1-click Rejection.
     */
    public function reject(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->status = 2;
        $reason = $request->input('rejection_reason', 'Not approved by manager');
        $leave->save();

        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);
        $durationDays = $start->diffInDays($end) + 1;
        $year = (int) $start->format('Y');

        try {
            $balance = EmployeeLeaveBalance::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type)
                ->where('year', $year)
                ->first();

            if ($balance) {
                $balance->decrement('pending_days', min($balance->pending_days, $durationDays));
            }
        } catch (\Throwable $e) {}

        EmployeeHistory::log(
            $leave->employee_id,
            'status_change',
            'Leave Rejected',
            "Rejected leave request \"{$leave->title}\". Note: {$reason}."
        );

        return back()->with('success', "Leave request marked as rejected.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $leave->delete();

        return back()->with('success', 'Leave record deleted successfully.');
    }
}
