<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumNotification;
use Illuminate\Http\Request;

class ForumMessageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.active.user']);
    }

    // GET /forums/messages?unread=1
    public function index(Request $request)
    {
        $user = $request->user();

        $query = ForumNotification::where('user_id', $user->id)->latest();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        $items = $query->limit(50)->get();

        $unreadCount = ForumNotification::where('user_id', $user->id)->whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'unreadCount' => $unreadCount,
            'items' => $items->map(function ($n) {
                return [
                    'id' => $n->id,
                    'message' => $n->message,
                    'url' => $n->url,
                    'read_at' => optional($n->read_at)->toISOString(),
                    'created_at_human' => $n->created_at?->diffForHumans(),
                    'created_at' => optional($n->created_at)->toISOString(),
                ];
            }),
        ]);
    }

    // POST /forums/messages/{id}/read
    public function markAsRead(Request $request, ForumNotification $notification)
    {
        if ((int) $notification->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // POST /forums/messages/{id}/unread
    public function markAsUnread(Request $request, ForumNotification $notification)
    {
        if ((int) $notification->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $notification->update(['read_at' => null]);

        return response()->json(['success' => true]);
    }

    // POST /forums/messages/mark-all-read
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        ForumNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
