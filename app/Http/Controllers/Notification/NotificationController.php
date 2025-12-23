<?php

namespace App\Http\Controllers\Notification;

use App\Models\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display notifications page
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, unread, read

        $query = Notification::where('user_id', auth()->id())
            ->recent();

        switch ($filter) {
            case 'unread':
                $query->unread();
                break;
            case 'read':
                $query->read();
                break;
        }

        $notifications = $query->paginate(20);

        // Count stats
        $stats = [
            'all' => Notification::where('user_id', auth()->id())->count(),
            'unread' => Notification::where('user_id', auth()->id())->unread()->count(),
            'read' => Notification::where('user_id', auth()->id())->read()->count(),
        ];

        return view('notifications.index', compact('notifications', 'filter', 'stats'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Authorization
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification)
    {
        // Authorization
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsUnread();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as unread.',
            ]);
        }

        return back()->with('success', 'Notification marked as unread.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = Notification::markAllAsRead(auth()->id());

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} notifications marked as read.",
                'count' => $count,
            ]);
        }

        return back()->with('success', "{$count} notifications marked as read.");
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        // Authorization
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted.',
            ]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Batch delete selected notifications
     */
    public function batchDelete(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'exists:notifications,id',
        ]);

        $count = Notification::where('user_id', auth()->id())
            ->whereIn('id', $request->notification_ids)
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} notifications deleted.",
                'count' => $count,
            ]);
        }

        return back()->with('success', "{$count} notifications deleted.");
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $count = Notification::deleteAllRead(auth()->id());

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} read notifications deleted.",
                'count' => $count,
            ]);
        }

        return back()->with('success', "{$count} read notifications deleted.");
    }

    /**
     * Show notification detail
     */
    public function show(Notification $notification)
    {
        // Authorization
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        // Mark as read
        $notification->markAsRead();

        return view('notifications.show', compact('notification'));
    }

    /**
     * Get unread count (for navbar badge)
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}