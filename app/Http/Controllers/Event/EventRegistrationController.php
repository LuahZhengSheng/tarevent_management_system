<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Support\PhoneHelper;
use App\Enums\ProgramType;
use App\Enums\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller {

    /**
     * Show registration form
     */
    public function create(Event $event) {
        // Authorization: Must be student/user
        if (!auth()->user()->hasRole('student')) {
            abort(403, 'Only students can register for events.');
        }

        // 检查是否有未完成的订单
        $pendingReg = EventRegistration::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->where('status', 'pending_payment')
                ->where('expires_at', '>', now())
                ->first();

        if ($pendingReg) {
            return redirect()
                            ->route('registrations.payment', $pendingReg)
                            ->with('info', 'You have an incomplete registration. Please complete payment within ' . (int) $pendingReg->remaining_minutes . ' minutes.');
        }

        // Check if event allows registration
        if (!$event->is_registration_open) {
            return redirect()
                            ->route('events.show', $event)
                            ->with('error', 'Registration is not currently open for this event.');
        }

        // Check if user can register
        if (!$event->canUserRegister(auth()->user())) {
            if (!$event->is_public && !$event->isUserClubMember(auth()->user())) {
                return redirect()
                                ->route('events.show', $event)
                                ->with('error', 'This is a private event. Only club members can register.');
            }

            return redirect()
                            ->route('events.show', $event)
                            ->with('error', 'You cannot register for this event at this time.');
        }

        // Check if user already registered
        $existingRegistration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->whereIn('status', ['confirmed', 'pending_payment'])
                ->first();

        if ($existingRegistration) {
            return redirect()
                            ->route('events.show', $event)
                            ->with('info', 'You are already registered for this event.');
        }

        // Check if event is full
        if ($event->is_full) {
            return redirect()
                            ->route('events.show', $event)
                            ->with('warning', 'This event is currently full.');
        }

        // Load custom registration fields
        $event->load('customRegistrationFields');

        // Prefill user data
        $userData = [
            'full_name' => auth()->user()->full_name ?? '',
            'email' => auth()->user()->email ?? '',
            'student_id' => auth()->user()->student_id ?? '',
            'phone' => auth()->user()->phone ? PhoneHelper::formatForDisplay(auth()->user()->phone) : '',
            'program' => auth()->user()->program ?? '',
        ];

        $programOptions = ProgramType::options();

        return view('events.register', compact('event', 'userData', 'programOptions'));
    }

    /**
     * Store registration
     */
    public function store(Request $request, Event $event) {
        try {
            // Validation and authorization checks...
            if (!$event->canUserRegister(auth()->user())) {
                return back()->with('error', 'You cannot register for this event.');
            }

            // Trim inputs
            $input = $request->all();
            foreach (['full_name', 'email', 'phone', 'student_id', 'program',
        'emergency_contact_name', 'emergency_contact_phone',
        'dietary_requirements', 'special_requirements'] as $key) {
                if (isset($input[$key])) {
                    $input[$key] = trim((string) $input[$key]);
                }
            }

            // Build validation rules
            $rules = [
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string',
                'program' => 'required|string|max:255',
                'terms_accepted' => 'required|accepted',
            ];

            if ($event->require_emergency_contact) {
                $rules['emergency_contact_name'] = 'required|string|max:255';
                $rules['emergency_contact_phone'] = 'required|string';
            }

            if ($event->require_dietary_info) {
                $rules['dietary_requirements'] = 'required|string|max:500';
            }

            if ($event->require_special_requirements) {
                $rules['special_requirements'] = 'required|string|max:500';
            }

            // Validate custom fields
            $customFields = $event->customRegistrationFields;
            foreach ($customFields as $field) {
                $fieldName = "custom_fields.{$field->name}";
                $rules[$fieldName] = $field->getValidationRulesArray();
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Phone validation
            $phoneError = PhoneHelper::getValidationError($request->phone);
            if ($phoneError) {
                return back()->withErrors(['phone' => $phoneError])->withInput();
            }

            if ($event->require_emergency_contact) {
                $emergencyPhoneError = PhoneHelper::getValidationError($request->emergency_contact_phone);
                if ($emergencyPhoneError) {
                    return back()->withErrors(['emergency_contact_phone' => $emergencyPhoneError])->withInput();
                }
            }

            // Rate limiting
            $ip = $request->ip();
            $userId = auth()->id();

            $ipCount = EventRegistration::where('ip_address', $ip)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->count();

            if ($ipCount >= 3) {
                return back()
                                ->withInput()
                                ->with('error', 'Too many registrations from your network. Please try again later.');
            }

            // 考虑 pending_payment 也算一次尝试
            $userEventAttempts = EventRegistration::where('user_id', $userId)
                    ->where('event_id', $event->id)
                    ->where(function ($q) {
                        $q->where('status', '!=', 'cancelled')
                        ->orWhere('created_at', '>=', now()->subMinutes(5));
                    })
                    ->count();

            if ($userEventAttempts >= 3) { // 允许3次尝试
                return back()
                                ->withInput()
                                ->with('error', 'You have made too many registration attempts for this event. Please try again later.');
            }

            $userEventCount = EventRegistration::where('user_id', $userId)
                    ->where('event_id', $event->id)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->count();

            if ($userEventCount >= 1) {
                return back()
                                ->withInput()
                                ->with('error', 'You have already tried to register for this event recently. Please wait a while.');
            }

            // Security checks
            if (!$event->is_registration_open) {
                return back()->with('error', 'Registration is not currently open.');
            }

            $existing = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $userId)
                    ->whereIn('status', ['confirmed', 'pending_payment'])
                    ->first();

            if ($existing) {
                return back()->with('error', 'You are already registered for this event.');
            }

            DB::beginTransaction();

            // 锁定 event 行，防止超卖
            $event = Event::lockForUpdate()->findOrFail($event->id);

            // 检查是否有未过期的 pending 订单
            $existingPending = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $userId)
                    ->where('status', 'pending_payment')
                    ->where('expires_at', '>', now())
                    ->first();

            if ($existingPending) {
                // 直接跳转到付款页
                return redirect()
                                ->route('registrations.payment', $existingPending)
                                ->with('info', 'You have an existing registration pending payment. Please complete it first.');
            }

            // 检查库存（包含 pending_payment 的人数）
            $occupied = $event->registrations()
                    ->whereIn('status', ['confirmed', 'pending_payment'])
                    ->count();

            if ($event->max_participants && $occupied >= $event->max_participants) {
                return back()->with('error', 'Event is fully booked.');
            }

            // Determine status based on payment requirement and availability
            $status = 'confirmed'; // Default for free events

            if ($event->is_paid) {
                $status = 'pending_payment'; // Paid events need payment
            } elseif ($event->is_full) {
                $status = 'waitlisted'; // Free but full events
            }

            // Build registration data
            $registrationData = [];

            if ($request->filled('dietary_requirements')) {
                $registrationData['dietary_requirements'] = strip_tags($request->dietary_requirements);
            }
            if ($request->filled('special_requirements')) {
                $registrationData['special_requirements'] = strip_tags($request->special_requirements);
            }

            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $key => $value) {
                    if (is_array($value)) {
                        $registrationData[$key] = array_map(function ($v) {
                            return trim(strip_tags($v));
                        }, $value);
                    } else {
                        $registrationData[$key] = trim(strip_tags($value));
                    }
                }
            }

            $user = auth()->user();

            $registrationInput = [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $status,
                'full_name' => strip_tags($request->full_name),
                'email' => $user->email,
                'phone' => PhoneHelper::formatForStorage($request->phone),
                'student_id' => $user->student_id,
                'program' => strip_tags($request->program),
                'registration_data' => $registrationData,
                'emergency_contact_name' => null,
                'emergency_contact_phone' => null,
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
            ];

            if ($event->require_emergency_contact) {
                $registrationInput['emergency_contact_name'] = strip_tags($request->emergency_contact_name);
                $registrationInput['emergency_contact_phone'] = PhoneHelper::formatForStorage($request->emergency_contact_phone);
            }

            $registrationInput['expires_at'] = now()->addMinutes(30);
            $registrationInput['payment_gateway'] = null; // 稍后在支付页选择
            $registrationInput['gateway_session_id'] = null;
            $registrationInput['expiry_notified'] = false;

            $registration = EventRegistration::create($registrationInput);

            DB::commit();

            Log::info('Event registration successful', [
                'registration_id' => $registration->id,
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => $status,
                'is_paid' => $event->is_paid,
            ]);

            // Redirect based on status
            if ($status === 'pending_payment') {
                // Redirect to payment page for paid events
                return redirect()
                                ->route('registrations.payment', $registration)
                                ->with('success', 'Registration received! Please complete payment to confirm your spot.');
            } elseif ($status === 'waitlisted') {
                return redirect()
                                ->route('events.show', $event)
                                ->with('info', 'Event is full. You have been added to the waitlist.');
            } else {
                // Free event - confirmed immediately
                return redirect()
                                ->route('events.my')
                                ->with('success', 'Registration confirmed! 🎉');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Event registration failed', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                            ->withInput()
                            ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Display user's registered events (My Events page)
     */
    public function myEvents() {
        $categories = EventCategory::values();

        return view('events.my-events', compact('categories'));
    }

    /**
     * Fetch user's registered events via AJAX
     */
    public function fetchMyEvents(Request $request) {
        try {
            $user = auth()->user();

            // Get user's registrations with related event data
            $registrations = $user->eventRegistrations()
                    ->with(['event' => function ($query) {
                            $query->with('organizer');
                        }, 'payment'])
                    ->whereHas('event') // Only include registrations with existing events
                    ->whereIn('status', ['confirmed', 'pending_payment', 'waitlisted'])
                    ->get();

            $now = now();
            $events = [];

            foreach ($registrations as $registration) {
                $event = $registration->event;

                // Skip if event is null (soft deleted)
                if (!$event) {
                    continue;
                }

                // Determine event status
                $status = 'upcoming';
                if ($event->status === 'cancelled') {
                    $status = 'cancelled';
                } elseif ($now >= $event->start_time && $now <= $event->end_time) {
                    $status = 'ongoing';
                } elseif ($now > $event->end_time) {
                    $status = 'past';
                }

                // Check if user can cancel registration
                $canCancel = false;
                if ($event->allow_cancellation &&
                        $registration->status === 'confirmed' &&
                        $status !== 'past' &&
                        $status !== 'cancelled') {

                    // Check cancellation deadline (e.g., 24 hours before event)
                    $cancellationDeadline = $event->start_time->subHours(24);
                    if ($now < $cancellationDeadline) {
                        $canCancel = true;
                    }
                }

                $events[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'venue' => $event->venue,
                    'venue_short' => Str::limit($event->venue, 30),
                    'category' => $event->category,
                    'is_paid' => $event->is_paid,
                    'fee_amount' => $event->fee_amount,
                    'poster_path' => $event->poster_path,
                    'start_time' => $event->start_time->toISOString(),
                    'end_time' => $event->end_time->toISOString(),
                    'status' => $status,
                    'registration_id' => $registration->id,
                    'registration_status' => $registration->status,
                    'registered_at' => $registration->created_at->toISOString(),
                    'payment_status' => $registration->payment ? $registration->payment->status : null,
                    'organizer_name' => $event->organizer->name ?? 'TARCampus',
                    'can_cancel' => $canCancel,
                ];
            }

            // 定义状态优先级 (数字越小越靠前)
            $statusPriority = [
                'ongoing' => 1,
                'upcoming' => 2,
                'past' => 3,
                'cancelled' => 4,
            ];

            // 使用 Laravel Collection 进行排序
            $sortedEvents = collect($events)->sort(function ($a, $b) use ($statusPriority) {
                        // 1. 先按状态优先级排序
                        $priorityA = $statusPriority[$a['status']] ?? 99;
                        $priorityB = $statusPriority[$b['status']] ?? 99;

                        if ($priorityA !== $priorityB) {
                            return $priorityA <=> $priorityB;
                        }

                        // 2. 状态相同，按时间排序
                        // Ongoing/Upcoming: 按开始时间 (越早越靠前)
                        // Past: 按结束时间 (越晚越靠前，即最近结束的在前面)
                        if ($a['status'] === 'past') {
                            return $b['end_time'] <=> $a['end_time']; // 降序
                        } else {
                            return $a['start_time'] <=> $b['start_time']; // 升序
                        }
                    })->values()->all();

            // Calculate statistics
            $stats = [
                'ongoing' => collect($events)->where('status', 'ongoing')->count(),
                'upcoming' => collect($events)->where('status', 'upcoming')->count(),
                'past' => collect($events)->where('status', 'past')->count(),
                'cancelled' => collect($events)->where('status', 'cancelled')->count(),
                'total' => count($events),
            ];

            return response()->json([
                        'success' => true,
                        'events' => $sortedEvents,
                        'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch my events error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch your events.',
                        'error' => config('app.debug') ? $e->getMessage() : null,
                            ], 500);
        }
    }

    /**
     * Cancel registration
     */
    public function destroy(EventRegistration $registration) {
        // Authorization: Must be the registration owner
        if (!$registration->belongsToUser(auth()->id())) {
            if (request()->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'You can only cancel your own registration.',
                                ], 403);
            }
            abort(403, 'You can only cancel your own registration.');
        }

        // --- 逻辑分流：区分 Pending 和 Confirmed ---
        $isPending = $registration->status === 'pending_payment';
        $refundReason = null;

        // 1. 只有 Confirmed 状态才需要检查活动规则
        if (!$isPending) {
            // Check if event allows cancellation
            if (!$registration->event->allow_cancellation) {
                $msg = 'This event does not allow registration cancellation.';
                return request()->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
            }

            // Check if cancellation is allowed (time-based)
            if (!$registration->can_be_cancelled) {
                $reason = $this->getCancellationBlockReason($registration);
                return request()->expectsJson() ? response()->json(['success' => false, 'message' => $reason], 422) : back()->with('error', $reason);
            }

            // Frequency Limit: Once per day
            $userId = auth()->id();
            $eventId = $registration->event_id;
            $cancelCountToday = EventRegistration::where('user_id', $userId)
                    ->where('event_id', $eventId)
                    ->where('status', 'cancelled')
                    ->whereDate('cancelled_at', now()->toDateString())
                    ->count();

            if ($cancelCountToday >= 1) {
                $message = 'You have already cancelled your registration for this event today. Please try again tomorrow.';
                return request()->expectsJson() ? response()->json(['success' => false, 'message' => $message], 422) : back()->with('error', $message);
            }

            // 验证退款理由
            if ($registration->event->is_paid &&
                    $registration->event->refund_available) {

                // 确保用户填了理由 (如果是 Form Submit)
                if (request()->filled('refund_reason')) {
                    $refundReason = strip_tags(request('refund_reason'));
                    if (strlen($refundReason) < 10) {
                        $msg = 'Refund reason must be at least 10 characters.';
                        return request()->expectsJson() ? response()->json(['success' => false, 'message' => $msg], 422) : back()->with('error', $msg);
                    }
                } else {
                    if (request()->isMethod('post') || request()->isMethod('delete')) {
                        $msg = 'Please provide a reason for the refund.';
                        // return request()->expectsJson() ? ... : ...;
                        // 注意：如果你的前端 form 还没传过来，这里可能会误拦。
                        // 建议：如果没填，给个默认理由 'User Cancelled'
                        $refundReason = 'User cancelled registration (No reason provided)';
                    }
                }
            }
        }

        try {
            DB::beginTransaction();

            // Cancel the registration
            $cancelReason = $isPending ? 'Order cancelled by user' : 'Cancelled by user';
            $registration->cancel($cancelReason);

            // 如果有退款逻辑，更新 Payment 表
            if (!$isPending &&
                    $registration->event->is_paid &&
                    $registration->event->refund_available &&
                    $registration->payment) {

                // 1. 更新 Payment 表 (创建退款申请)
                // 确保你的 Payment Model 有 requestRefund 方法
                // 或者直接 update
                if (method_exists($registration->payment, 'requestRefund')) {
                    $registration->payment->requestRefund($refundReason ?? 'User Cancelled', auth()->id());
                } else {
                    // Fallback update
                    $registration->payment->update([
                        'refund_status' => 'pending',
                        'refund_reason' => $refundReason ?? 'User Cancelled',
                        'refund_requested_at' => now(),
                        'refund_requested_by' => auth()->id(),
                    ]);
                }

                // 2. 更新 Registration 表
                $registration->update([
                    'refund_status' => 'pending',
                    'refund_requested_at' => now(),
                    'refund_auto_reject_at' => now()->addDays(7),
                ]);
            }

            DB::commit();

            Log::info('Registration cancelled by user', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'status_before' => $isPending ? 'pending' : 'confirmed',
                'refund_status' => $registration->refund_status
            ]);

            $successMsg = $isPending ? 'Order cancelled successfully.' : 'Registration cancelled successfully.' . ($registration->refund_status === 'pending' ? ' Your refund request has been submitted.' : '');

            if (request()->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => $successMsg,
                            'redirect' => route('events.show', $registration->event),
                ]);
            }

            return redirect()
                            ->route('events.show', $registration->event)
                            ->with('success', $successMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration cancellation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to cancel registration: ' . $e->getMessage());
        }
    }

    /**
     * Get human-readable cancellation block reason
     */
    private function getCancellationBlockReason(EventRegistration $registration) {
        $now = now();
        $event = $registration->event;

        if ($registration->status === 'cancelled') {
            return 'This registration has already been cancelled.';
        }

        if (!in_array($registration->status, ['confirmed', 'pending_payment'])) {
            return 'Only confirmed registrations can be cancelled.';
        }

        if ($now < $event->registration_start_time) {
            return 'Registration period has not started yet.';
        }

        if ($now > $event->registration_end_time) {
            return 'The cancellation deadline has passed. Registration period ended on ' .
                    $event->registration_end_time->format('d M Y, h:i A') . '.';
        }

        if ($now >= $event->start_time) {
            return 'Cannot cancel after the event has started.';
        }

        return 'This registration cannot be cancelled at this time.';
    }

    /**
     * Show registration history for a specific event
     */
    public function history(Event $event) {
        // Check if user has any registrations for this event
        $hasRegistrations = EventRegistration::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->exists();

        if (!$hasRegistrations) {
            return redirect()
                            ->route('events.show', $event)
                            ->with('info', 'You have no registration history for this event.');
        }

        return view('events.registration-history', compact('event'));
    }

    /**
     * Fetch registration history via AJAX
     */
    public function fetchHistory(Request $request, Event $event) {
        try {
            $query = EventRegistration::where('event_id', $event->id)
                    ->where('user_id', auth()->id())
                    ->with(['payment']);

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Sort
            $sort = $request->input('sort', 'recent');
            if ($sort === 'recent') {
                $query->orderBy('created_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }

            $registrations = $query->get();

            $formattedRegistrations = $registrations->map(function ($reg) {
                return [
            'id' => $reg->id,
            'registration_number' => $reg->registration_number,
            'status' => $reg->status,
            'full_name' => $reg->full_name,
            'email' => $reg->email,
            'phone' => $reg->phone,
            'student_id' => $reg->student_id,
            'program' => $reg->program,
            'created_at' => $reg->created_at->toISOString(),
            'cancelled_at' => $reg->cancelled_at ? $reg->cancelled_at->toISOString() : null,
            'payment_status' => $reg->payment ? $reg->payment->status : null,
            'refund_status' => $reg->refund_status,
            'payment' => $reg->payment ? [
        'id' => $reg->payment->id,
        'amount' => $reg->payment->amount,
        'method' => $reg->payment->method,
        'status' => $reg->payment->status,
        'paid_at' => $reg->payment->paid_at ? $reg->payment->paid_at->toISOString() : null,
        'transaction_id' => $reg->payment->transaction_id,
        'refund_amount' => $reg->payment->refund_amount,
        'refund_status' => $reg->payment->refund_status,
        'refund_reason' => $reg->payment->refund_reason,
        'refund_requested_at' => $reg->payment->refund_requested_at ? $reg->payment->refund_requested_at->toISOString() : null,
        'refund_processed_at' => $reg->payment->refund_processed_at ? $reg->payment->refund_processed_at->toISOString() : null,
        'refund_rejection_reason' => $reg->payment->refund_rejection_reason,
            ] : null,
                ];
            });

            return response()->json([
                        'success' => true,
                        'registrations' => $formattedRegistrations,
            ]);
        } catch (\Exception $e) {
            Log::error('Fetch registration history error', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch registration history.',
                            ], 500);
        }
    }

    /**
     * AJAX field validation
     */
    public function validateField(Request $request) {
        $field = $request->input('field');
        $value = $request->input('value');
        $eventId = $request->input('event_id');

        $rules = [
            'full_name' => 'required|string|max:255',
//            'email' => 'required|email|max:255',
            'phone' => 'required|string|regex:/^[0-9\+\-\(\)\s]+$/|max:20',
//            'student_id' => 'required|string|max:50',
            'program' => 'required|string|max:255',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|regex:/^[0-9\+\-\(\)\s]+$/|max:20',
        ];

        // Check if it's a custom field
        if (strpos($field, 'custom_fields.') === 0) {
            $fieldName = str_replace('custom_fields.', '', $field);

            if ($eventId) {
                $event = Event::find($eventId);
                if ($event) {
                    $customField = $event->customRegistrationFields()
                            ->where('name', $fieldName)
                            ->first();

                    if ($customField) {
                        $rules[$field] = $customField->getValidationRulesArray();
                    }
                }
            }
        }

        if (!isset($rules[$field])) {
            return response()->json(['valid' => true]);
        }

        $validator = Validator::make(
                        [$field => $value],
                        [$field => $rules[$field]]
        );

        if ($validator->fails()) {
            return response()->json([
                        'valid' => false,
                        'message' => $validator->errors()->first($field),
            ]);
        }

        return response()->json(['valid' => true]);
    }
}
