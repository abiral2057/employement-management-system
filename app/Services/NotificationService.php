<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeHistory;
use App\Models\LateTime;
use App\Models\Leave;
use App\Models\OverTime;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Get all active system notifications.
     */
    public function getAllNotifications(): array
    {
        $notifications = [];
        $user = Auth::user();
        $roleSlug = $user && $user->role ? $user->role->slug : 'hr';

        // 1. Pending Leave Requests
        $pendingLeaves = Leave::with(['employee.department', 'employee.designation'])
            ->where(function ($q) {
                $q->where('status', 0)
                  ->orWhere('status', '0')
                  ->orWhere('status', 'pending');
            })
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        foreach ($pendingLeaves as $leave) {
            $empName = $leave->employee ? $leave->employee->firstname . ' ' . $leave->employee->lastname : 'Employee #' . $leave->employee_id;
            $sDate = $leave->start_date ?? $leave->from_date;
            $eDate = $leave->end_date ?? $leave->to_date;
            $days = ($sDate && $eDate) ? Carbon::parse($sDate)->diffInDays(Carbon::parse($eDate)) + 1 : 1;
            
            $leaveUrl = $roleSlug === 'super-admin' 
                ? route('leaves.index') 
                : ($roleSlug === 'administrator' ? route('admin.leaves.index') : route('hr.leaves.index'));

            $notifications[] = [
                'id' => 'leave_' . $leave->id,
                'category' => 'leave',
                'type_label' => 'Leave Request',
                'title' => 'Leave Approval Required',
                'message' => "{$empName} requested {$days} day(s) for {$leave->title}.",
                'icon' => 'fa-solid fa-person-walking-arrow-right',
                'feather' => 'calendar',
                'color' => 'warning',
                'badge' => 'Pending Review',
                'priority' => 'high',
                'action_url' => $leaveUrl,
                'action_label' => 'Review Leave',
                'created_at' => $leave->created_at ?? now(),
                'time_ago' => ($leave->created_at ? Carbon::parse($leave->created_at)->diffForHumans() : 'Recently'),
            ];
        }

        // 2. Pending Overtime Claims
        $pendingOvertimes = OverTime::with('employee')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        foreach ($pendingOvertimes as $ot) {
            $empName = $ot->employee ? $ot->employee->firstname . ' ' . $ot->employee->lastname : 'Employee #' . $ot->employee_id;
            $notifications[] = [
                'id' => 'ot_' . $ot->id,
                'category' => 'overtime',
                'type_label' => 'Overtime Claim',
                'title' => 'Overtime Payout Pending',
                'message' => "{$empName} logged {$ot->overtime_duration} ({$ot->hours_decimal} hrs) for € " . number_format($ot->amount ?? 0, 2) . ".",
                'icon' => 'fa-solid fa-stopwatch',
                'feather' => 'clock',
                'color' => 'warning',
                'badge' => 'Claim Pending',
                'priority' => 'medium',
                'action_url' => route('attendance.overtime'),
                'action_label' => 'Approve / Payout',
                'created_at' => $ot->created_at ?? now(),
                'time_ago' => ($ot->created_at ? Carbon::parse($ot->created_at)->diffForHumans() : 'Recently'),
            ];
        }

        // 3. Overdue Salaries & Outstanding Balance Dues
        $overduePayrolls = Payroll::with(['employee.department'])
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->orderBy('due_date', 'asc')
            ->take(10)
            ->get();

        $months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];

        foreach ($overduePayrolls as $p) {
            $empName = $p->employee ? $p->employee->firstname . ' ' . $p->employee->lastname : 'Employee #' . $p->employee_id;
            $monthName = $months[$p->month] ?? "Month {$p->month}";
            $balance = $p->balance_due;

            if ($balance > 0) {
                $notifications[] = [
                    'id' => 'payroll_overdue_' . $p->id,
                    'category' => 'payroll',
                    'type_label' => 'Salary Overdue',
                    'title' => 'Payment Overdue Alert',
                    'message' => "{$empName} has an outstanding salary due of € " . number_format($balance, 2) . " for {$monthName} {$p->year}.",
                    'icon' => 'fa-solid fa-triangle-exclamation',
                    'feather' => 'alert-circle',
                    'color' => 'danger',
                    'badge' => 'Overdue',
                    'priority' => 'urgent',
                    'action_url' => route('payroll.due') . "?year={$p->year}&month={$p->month}",
                    'action_label' => 'Settle Due',
                    'created_at' => $p->due_date ? Carbon::parse($p->due_date) : now(),
                    'time_ago' => ($p->due_date ? Carbon::parse($p->due_date)->diffForHumans() : 'Overdue'),
                ];
            }
        }

        // 4. Expiring and Expired Employee Documents
        $criticalDocs = EmployeeDocument::with('employee')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', today()->addDays(30))
            ->orderBy('expiry_date', 'asc')
            ->take(10)
            ->get();

        foreach ($criticalDocs as $doc) {
            $empName = $doc->employee ? $doc->employee->firstname . ' ' . $doc->employee->lastname : 'Employee #' . $doc->employee_id;
            $isExpired = $doc->isExpired();
            $empShowUrl = $roleSlug === 'super-admin'
                ? route('employee.show', $doc->employee_id)
                : ($roleSlug === 'administrator' ? route('admin.employee.show', $doc->employee_id) : route('hr.employee.show', $doc->employee_id));

            $notifications[] = [
                'id' => 'doc_' . $doc->id,
                'category' => 'document',
                'type_label' => $isExpired ? 'Expired Document' : 'Document Expiring Soon',
                'title' => $isExpired ? 'Document Expired' : 'Document Renewal Due',
                'message' => "{$empName}'s {$doc->title} " . ($isExpired ? 'expired on ' . $doc->expiry_date->format('d M Y') : 'expires in ' . today()->diffInDays($doc->expiry_date) . ' days.'),
                'icon' => 'fa-solid fa-file-shield',
                'feather' => 'file-text',
                'color' => $isExpired ? 'danger' : 'warning',
                'badge' => $isExpired ? 'Expired' : 'Expiring Soon',
                'priority' => $isExpired ? 'urgent' : 'medium',
                'action_url' => $empShowUrl,
                'action_label' => 'View Document',
                'created_at' => $doc->expiry_date ? Carbon::parse($doc->expiry_date) : now(),
                'time_ago' => ($doc->expiry_date ? Carbon::parse($doc->expiry_date)->diffForHumans() : 'Action needed'),
            ];
        }

        // 5. Today's Late Time Arrivals
        $todayLateCount = LateTime::whereDate('created_at', today())
            ->orWhereDate('latetime_date', today())
            ->count();

        if ($todayLateCount > 0) {
            $notifications[] = [
                'id' => 'late_today_' . today()->toDateString(),
                'category' => 'attendance',
                'type_label' => 'Attendance Alert',
                'title' => 'Late Arrivals Recorded Today',
                'message' => "{$todayLateCount} employee(s) were flagged as late for their assigned shifts today.",
                'icon' => 'fa-solid fa-clock-rotate-left',
                'feather' => 'user-x',
                'color' => 'info',
                'badge' => 'Today',
                'priority' => 'info',
                'action_url' => route('attendance.latetime'),
                'action_label' => 'Review Late Arrivals',
                'created_at' => today(),
                'time_ago' => 'Today',
            ];
        }

        // 6. Recent Activity Log Audits
        $recentHistories = EmployeeHistory::with('employee')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentHistories as $hist) {
            $empName = $hist->employee ? $hist->employee->firstname . ' ' . $hist->employee->lastname : 'Staff';
            $notifications[] = [
                'id' => 'history_' . $hist->id,
                'category' => 'activity',
                'type_label' => 'Activity Audit',
                'title' => $hist->title,
                'message' => "{$empName}: " . Str::limit($hist->description, 60),
                'icon' => 'fa-solid fa-timeline',
                'feather' => 'activity',
                'color' => 'primary',
                'badge' => 'Audit Log',
                'priority' => 'low',
                'action_url' => $hist->employee_id ? ($roleSlug === 'super-admin' ? route('employee.show', $hist->employee_id) : ($roleSlug === 'administrator' ? route('admin.employee.show', $hist->employee_id) : route('hr.employee.show', $hist->employee_id))) : '#',
                'action_label' => 'View Profile',
                'created_at' => $hist->created_at ? Carbon::parse($hist->created_at) : now(),
                'time_ago' => ($hist->created_at ? Carbon::parse($hist->created_at)->diffForHumans() : 'Recent'),
            ];
        }

        // Sort: Urgent -> High -> Medium -> Low, then newest first
        $priorityOrder = ['urgent' => 1, 'high' => 2, 'medium' => 3, 'info' => 4, 'low' => 5];
        usort($notifications, function ($a, $b) use ($priorityOrder) {
            $pa = $priorityOrder[$a['priority']] ?? 99;
            $pb = $priorityOrder[$b['priority']] ?? 99;
            if ($pa === $pb) {
                return Carbon::parse($b['created_at'])->timestamp <=> Carbon::parse($a['created_at'])->timestamp;
            }
            return $pa <=> $pb;
        });

        // Mark read/unread flag
        $readIds = session('read_notification_ids', []);
        foreach ($notifications as &$item) {
            $item['is_read'] = in_array($item['id'], $readIds);
        }

        return $notifications;
    }

    /**
     * Get unread notifications only.
     */
    public function getUnreadNotifications(): array
    {
        return array_values(array_filter($this->getAllNotifications(), fn($n) => !$n['is_read']));
    }

    /**
     * Get count of unread notifications.
     */
    public function getUnreadCount(): int
    {
        return count($this->getUnreadNotifications());
    }

    /**
     * Get top items for navbar dropdown display.
     */
    public function getNavbarNotifications(int $limit = 6): array
    {
        $all = $this->getAllNotifications();
        return array_slice($all, 0, $limit);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id): void
    {
        $readIds = session('read_notification_ids', []);
        if (!in_array($id, $readIds)) {
            $readIds[] = $id;
            session(['read_notification_ids' => $readIds]);
        }
    }

    /**
     * Mark all active notifications as read.
     */
    public function markAllAsRead(): void
    {
        $all = $this->getAllNotifications();
        $ids = array_column($all, 'id');
        session(['read_notification_ids' => $ids]);
    }

    /**
     * Clear / reset dismissed state.
     */
    public function clearAll(): void
    {
        $this->markAllAsRead();
    }
}
