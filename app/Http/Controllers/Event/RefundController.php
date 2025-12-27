<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\RefundService;
use App\Services\NotificationService;
use App\Support\PdfHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller {

    protected $refundService;
    protected $notificationService;

    public function __construct(RefundService $refundService, NotificationService $notificationService) {
        $this->refundService = $refundService;
        $this->notificationService = $notificationService;
    }

    /**
     * Request refund (User-initiated)
     */
    public function request(Request $request, EventRegistration $registration) {
        // Authorization
        if ($registration->user_id !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized access.',
                                ], 403);
            }
            abort(403, 'Unauthorized access to this registration.');
        }

        // Validation
        $request->validate([
            'refund_reason' => 'required|string|min:10|max:500',
            'confirm' => 'required|accepted',
                ], [
            'refund_reason.required' => 'Please provide a reason for the refund request.',
            'refund_reason.min' => 'Refund reason must be at least 10 characters.',
            'confirm.accepted' => 'You must confirm that you understand the refund policy.',
        ]);

        try {
            $this->refundService->requestRefund($registration, $request->refund_reason);

            Log::info('Refund requested successfully', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Refund request submitted successfully. The organizer will review your request within 7 days.',
                            'redirect' => route('events.my'),
                ]);
            }

            return redirect()
                            ->route('events.my')
                            ->with('success', 'Refund request submitted successfully. The organizer will review your request within 7 days.');
        } catch (\Exception $e) {
            Log::error('Refund request failed', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => $e->getMessage(),
                                ], 422);
            }

            return back()
                            ->withInput()
                            ->with('error', $e->getMessage());
        }
    }

    /**
     * Approve refund (Organizer/Admin)
     */
    public function approve(Request $request, Payment $payment) {
        // Authorization check
        $user = auth()->user();
        $event = $payment->registration->event;

        $canApprove = $user->isAdmin() ||
                ($user->hasRole('club') && $event->organizer_id === $user->club_id);

        if (!$canApprove) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to approve this refund.',
                                ], 403);
            }
            abort(403, 'You do not have permission to approve this refund.');
        }

        try {
            $this->refundService->approveRefund($payment, auth()->id());

            // Send notification to user
            // Observer will send
            // $this->notificationService->sendRefundApproved($payment);

            Log::info('Refund approved successfully', [
                'payment_id' => $payment->id,
                'approved_by' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Refund has been approved and is being processed. The user will receive confirmation once completed.',
                ]);
            }

            return back()->with('success', 'Refund approved and processing. User will be notified once completed.');
        } catch (\Exception $e) {
            Log::error('Refund approval failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => $e->getMessage(),
                                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject refund (Organizer/Admin)
     */
    public function reject(Request $request, Payment $payment) {
        // Authorization check
        $user = auth()->user();
        $event = $payment->registration->event;

        $canReject = $user->isAdmin() ||
                ($user->hasRole('club') && $event->organizer_id === $user->club_id);

        if (!$canReject) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to reject this refund.',
                                ], 403);
            }
            abort(403, 'You do not have permission to reject this refund.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500',
                ], [
            'rejection_reason.required' => 'Please provide a reason for rejecting the refund.',
            'rejection_reason.min' => 'Rejection reason must be at least 10 characters.',
        ]);

        try {
            $this->refundService->rejectRefund($payment, $request->rejection_reason, auth()->id());

            // Send notification to user
            // Observer will send
            // $this->notificationService->sendRefundRejected($payment);

            Log::info('Refund rejected', [
                'payment_id' => $payment->id,
                'rejected_by' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Refund request has been rejected. User will be notified.',
                ]);
            }

            return back()->with('success', 'Refund request rejected. User has been notified.');
        } catch (\Exception $e) {
            Log::error('Refund rejection failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => $e->getMessage(),
                                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download refund receipt
     */
    public function downloadRefundReceipt(Payment $payment) {
        // Authorization
        if ($payment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        // Check if refund is completed
        if ($payment->refund_status !== 'completed') {
            return back()->with('error', 'Refund receipt is only available for completed refunds.');
        }

        try {
            return PdfHelper::generateRefundReceipt($payment, true);
        } catch (\Exception $e) {
            Log::error('Refund receipt download failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to generate refund receipt. Please try again.');
        }
    }

    /**
     * Show refund management page (for organizers/admins)
     */
    public function manage(Request $request) {
        $user = auth()->user();

        // Authorization: Must be club or admin
        if (!$user->hasRole('club') && !$user->isAdmin()) {
            abort(403, 'Only club organizers and administrators can access this page.');
        }

        return view('events.refund-management');
    }

    /**
     * Fetch refund requests (AJAX for organizers/admins)
     */
    public function fetchRequests(Request $request) {
        try {
            $user = auth()->user();

            $query = Payment::with(['registration.event', 'registration.user'])
                    ->whereNotNull('refund_status')
                    ->where('status', 'success');

            // Filter by organizer's events if not admin
            if (!$user->isAdmin() && $user->hasRole('club')) {
                $query->whereHas('registration.event', function ($q) use ($user) {
                    $q->where('organizer_id', $user->club_id)
                            ->where('organizer_type', 'club');
                });
            }

            // NEW: Filter by specific event if provided
            if ($request->filled('event_id')) {
                $query->whereHas('registration', function ($q) use ($request) {
                    $q->where('event_id', $request->event_id);
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('refund_status', $request->status);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('refund_requested_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('refund_requested_at', '<=', $request->date_to);
            }

            // Calculate stats before pagination
            $stats = [
                'pending' => (clone $query)->where('refund_status', 'pending')->count(),
                'processing' => (clone $query)->where('refund_status', 'processing')->count(),
                'completed' => (clone $query)->where('refund_status', 'completed')->count(),
                'rejected' => (clone $query)->where('refund_status', 'rejected')->count(),
            ];

            // Sort
            $sort = $request->input('sort', 'recent');
            if ($sort === 'recent') {
                $query->orderBy('refund_requested_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('refund_requested_at', 'asc');
            } elseif ($sort === 'amount_high') {
                $query->orderBy('refund_amount', 'desc');
            } elseif ($sort === 'amount_low') {
                $query->orderBy('refund_amount', 'asc');
            }

            $perPage = $request->input('per_page', 15);
            $refunds = $query->paginate($perPage);

            $formattedRefunds = $refunds->map(function ($payment) {
                return [
            'id' => $payment->id,
            'registration_number' => $payment->registration->registration_number,
            'event_title' => $payment->registration->event->title,
            'user_name' => $payment->registration->user->name,
            'user_email' => $payment->registration->user->email,
            'amount' => $payment->amount,
            'refund_amount' => $payment->refund_amount,
            'refund_status' => $payment->refund_status,
            'refund_reason' => $payment->refund_reason,
            'refund_requested_at' => $payment->refund_requested_at ? $payment->refund_requested_at->toISOString() : null,
            'refund_processed_at' => $payment->refund_processed_at ? $payment->refund_processed_at->toISOString() : null,
            'auto_reject_at' => $payment->registration->refund_auto_reject_at ? $payment->registration->refund_auto_reject_at->toISOString() : null,
            'days_remaining' => $payment->registration->refund_auto_reject_at ? now()->diffInDays($payment->registration->refund_auto_reject_at, false) : null,
                ];
            });

            return response()->json([
                        'success' => true,
                        'refunds' => $formattedRefunds,
                        'stats' => $stats,
                        'pagination' => [
                            'current_page' => $refunds->currentPage(),
                            'last_page' => $refunds->lastPage(),
                            'per_page' => $refunds->perPage(),
                            'total' => $refunds->total(),
                            'from' => $refunds->firstItem(),
                            'to' => $refunds->lastItem(),
                        ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Fetch refund requests error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch refund requests.',
                            ], 500);
        }
    }

    public function manageEventRefunds(Request $request, Event $event)
    {
        // ==========================================
        // 1. 权限检查 (Authorization)
        // ==========================================
        $user = auth()->user();

        // 如果不是管理员，且不是该活动的主办方，则报错 403
        $isOrganizer = $user->hasRole('club') && $event->organizer_id === $user->club_id;
        
        if (!$user->isAdmin() && !$isOrganizer) {
            abort(403, 'Unauthorized access to this event\'s refunds.');
        }
        
        // 额外检查：如果活动不是付费或不支持退款，也不应该进来
        if (!$event->is_paid || !$event->refund_available) {
             return redirect()->route('events.show', $event)
                    ->with('error', 'This event does not support refunds.');
        }

        // ==========================================
        // 2. 构建查询 (Scope & Filter)
        // ==========================================
        
        // 只查询 属于当前 $event 的 Payment
        $query = Payment::query()
            ->where('event_id', $event->id)
            // 只看有退款相关的记录 (pending, completed, rejected)
            ->whereNotNull('refund_status'); 

        // --- Filter: 搜索 (User Name or Email) ---
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('registration.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // --- Filter: 状态筛选 (Pending, Completed, Rejected) ---
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('refund_status', $request->input('status'));
        }

        // ==========================================
        // 3. 排序 (Sorting)
        // ==========================================
        $sort = $request->input('sort', 'latest'); // 默认 latest

        switch ($sort) {
            case 'oldest':
                $query->oldest('refund_requested_at');
                break;
            case 'amount_high':
                $query->orderByDesc('amount');
                break;
            case 'amount_low':
                $query->orderBy('amount');
                break;
            case 'latest':
            default:
                // 推荐用 refund_requested_at 或 updated_at
                $query->latest('updated_at'); 
                break;
        }

        // ==========================================
        // 4. 获取数据并返回视图
        // ==========================================
        $refunds = $query->with(['registration.user'])->paginate(10)->withQueryString();

        // 统计数据 (用于页面顶部的 Dashboard)
        $stats = [
            'total_refunded' => $event->payments()
                                ->where('refund_status', 'completed')
                                ->sum('amount'),
            'pending_count'  => $event->payments()
                                ->where('refund_status', 'pending')
                                ->count(),
        ];

        return view('events.refund-management', compact('event', 'refunds', 'stats'));
    }
}
