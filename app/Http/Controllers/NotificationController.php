<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the Notification Center dashboard.
     */
    public function index(Request $request)
    {
        $category = $request->input('category', 'all');
        $status = $request->input('status', 'all');

        $allNotifications = $this->notificationService->getAllNotifications();

        $notifications = array_filter($allNotifications, function ($item) use ($category, $status) {
            $categoryMatch = ($category === 'all') || ($item['category'] === $category);
            $statusMatch = ($status === 'all') || ($status === 'unread' && !$item['is_read']) || ($status === 'read' && $item['is_read']);
            return $categoryMatch && $statusMatch;
        });

        // Summary counts
        $counts = [
            'all' => count($allNotifications),
            'unread' => count(array_filter($allNotifications, fn($n) => !$n['is_read'])),
            'leave' => count(array_filter($allNotifications, fn($n) => $n['category'] === 'leave')),
            'overtime' => count(array_filter($allNotifications, fn($n) => $n['category'] === 'overtime')),
            'payroll' => count(array_filter($allNotifications, fn($n) => $n['category'] === 'payroll')),
            'document' => count(array_filter($allNotifications, fn($n) => $n['category'] === 'document')),
            'attendance' => count(array_filter($allNotifications, fn($n) => $n['category'] === 'attendance')),
        ];

        return view('admin.notifications.index', compact('notifications', 'counts', 'category', 'status'));
    }

    /**
     * Mark a single notification or all notifications as read.
     */
    public function markAsRead(Request $request)
    {
        $id = $request->input('id');

        if ($id === 'all') {
            $this->notificationService->markAllAsRead();
        } elseif ($id) {
            $this->notificationService->markAsRead($id);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $this->notificationService->getUnreadCount(),
                'message' => 'Notification updated successfully.',
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Clear / Dismiss all notifications.
     */
    public function clearAll(Request $request)
    {
        $this->notificationService->clearAll();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'message' => 'All notifications marked as read.',
            ]);
        }

        return back()->with('success', 'All notifications cleared.');
    }

    /**
     * Return live notifications data for AJAX polling or dropdown refresh.
     */
    public function fetchNavbar(Request $request)
    {
        $items = $this->notificationService->getNavbarNotifications(8);
        $unreadCount = $this->notificationService->getUnreadCount();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ]);
    }
}
