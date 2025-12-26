<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistrationField;
use App\Models\Club;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Support\MediaHelper;
use App\Enums\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class EventController extends Controller {

    /**
     * Display a listing of events (User Site)
     */
    public function index(Request $request) {
        $query = Event::published()
                ->upcoming()
                ->with(['organizer', 'registrations']);

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by search term
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                        ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }

        // Filter by fee type
        if ($request->filled('fee_type')) {
            if ($request->fee_type === 'free') {
                $query->where('is_paid', false);
            } elseif ($request->fee_type === 'paid') {
                $query->where('is_paid', true);
            }
        }

        $events = $query->orderBy('start_time')
                ->paginate(12);

        $categories = EventCategory::values();

        return view('events.index', compact('events', 'categories'));
    }

    /**
     * Display event details
     */
    public function show(Event $event) {
        // Check if user can view this event
        if ($event->status === 'draft' || $event->status === 'pending') {
            // Only organizer/admin can view draft/pending events
            if (!auth()->check() || (!$event->canBeEditedBy(auth()->user()) && !auth()->user()->isAdmin())) {
                abort(404);
            }
        }

        // Check if user is registered
        $isRegistered = false;
        $userRegistration = null;
        $hasHistory = false;

        if (auth()->check()) {
            $userRegistration = $event->registrations()
                    ->where('user_id', auth()->id())
                    // 优先找 active 的，如果找不到再找 cancelled 的
                    ->orderByRaw("FIELD(status, 'confirmed', 'pending_payment', 'cancelled')")
                    ->latest() // 或者取最新的
                    ->first();
            $isRegistered = $userRegistration !== null && $userRegistration->status !== 'cancelled';
            $hasHistory = $event->registrations()
                    ->where('user_id', auth()->id())
                    ->exists();
        }

        // Determine if user can manage this event
        $canManageEvent = false;
        $isManager = false;

        if (auth()->check()) {
            $user = auth()->user();
            $isManager = $user->isAdmin() || $user->hasRole('club');

            // 能不能看到管理按钮：管理员 或 这个 event 的 club 主办方
            $canManageEvent = $user->isAdmin() ||
                    ($user->hasRole('club') && $event->organizer_id === $user->club_id);
        }

        // Determine current stage
        $now = now();
        $stage = 'draft'; // default

        if ($event->status === 'cancelled') {
            $stage = 'cancelled';
        } elseif ($event->status === 'draft') {
            $stage = 'draft';
        } elseif ($event->status === 'pending') {
            $stage = 'pending';
        } elseif ($event->status === 'published') {
            if ($now < $event->registration_start_time) {
                $stage = 'pre_registration';
            } elseif ($now >= $event->registration_start_time && $now <= $event->registration_end_time) {
                if ($now < $event->start_time) {
                    $stage = 'registration_open';
                } elseif ($now >= $event->start_time && $now <= $event->end_time) {
                    $stage = 'ongoing';
                } else {
                    $stage = 'past';
                }
            } elseif ($now > $event->registration_end_time && $now < $event->start_time) {
                $stage = 'registration_closed';
            } elseif ($now >= $event->start_time && $now <= $event->end_time) {
                $stage = 'ongoing';
            } elseif ($now > $event->end_time) {
                $stage = 'past';
            }
        }

        // Calculate time differences for display
        $timeInfo = [
            'registration_starts_in' => $now < $event->registration_start_time ? $event->registration_start_time->diffForHumans() : null,
            'registration_ends_in' => $now < $event->registration_end_time ? $event->registration_end_time->diffForHumans() : null,
            'event_starts_in' => $now < $event->start_time ? $event->start_time->diffForHumans() : null,
            'event_ends_in' => $now < $event->end_time && $now >= $event->start_time ? $event->end_time->diffForHumans() : null,
            'event_ended' => $now > $event->end_time ? $event->end_time->diffForHumans() : null,
        ];

        // Check if user can register 
        $canRegister = false;
        $registrationBlockReason = null;

        if (auth()->check()) {
            $canRegister = $event->canUserRegister(auth()->user());

            // Determine why user cannot register
            if (!$canRegister && !$isRegistered) {
                if (!$event->is_public && !$event->isUserClubMember(auth()->user())) {
                    $registrationBlockReason = 'not_club_member';
                } elseif (!$event->is_registration_open) {
                    $registrationBlockReason = 'registration_closed';
                } elseif ($event->is_full) {
                    $registrationBlockReason = 'event_full';
                }
            }
        }

        return view('events.show', compact(
                        'event',
                        'isRegistered',
                        'userRegistration',
                        'hasHistory',
                        'canManageEvent',
                        'canRegister',
                        'registrationBlockReason',
                        'stage',
                        'timeInfo'
        ));
    }

    /**
     * Show the form for creating a new event (Club Admin Only)
     */
    public function create() {
        // Authorization: Must be club admin
//        if (!auth()->user()->hasRole('club')) {
//            abort(403, 'Only club administrators can create events.');
//        }

        $clubs = Club::where('status', 'active')->get();

        $categories = EventCategory::values();

        return view('events.create', compact('clubs', 'categories'));
    }

    /**
     * Store a newly created event
     */
    public function store(StoreEventRequest $request) {
        // Authorization already checked in StoreEventRequest

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle poster upload using MediaHelper
            if ($request->hasFile('poster')) {
                try {
                    $imageResult = MediaHelper::processImage(
                                    $request->file('poster'),
                                    'event-posters',
                                    [
                                        'max_size' => 5,
                                        'max_width' => 2000,
                                        'max_height' => 2000,
                                        'quality' => 85,
                                        'format' => 'jpg',
                                        'compress' => true,
                                        'thumbnail' => true,
                                        'thumbnail_width' => 400,
                                        'thumbnail_height' => 300,
                                    ]
                    );

                    $data['poster_path'] = $imageResult['filename'];
                    $data['poster_thumbnail_path'] = $imageResult['thumbnail_filename'] ?? null;

                    Log::info('Event poster processed', [
                        'path' => $imageResult['path'],
                        'size' => $imageResult['metadata']['size'],
                        'dimensions' => $imageResult['metadata']['width'] . 'x' . $imageResult['metadata']['height'],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Poster upload failed', [
                        'error' => $e->getMessage(),
                    ]);

                    return response()->json([
                                'success' => false,
                                'message' => 'Failed to process poster image: ' . $e->getMessage(),
                                    ], 422);
                }
            }

            // Set organizer info
            $data['organizer_id'] = $request->club_id ?? 1; // 之后可换成 auth()->user()->club_id
            $data['organizer_type'] = 'club';

            $data['allow_cancellation'] = $request->has('allow_cancellation') ? 1 : 0;
            $data['require_emergency_contact'] = $request->has('require_emergency_contact') ? 1 : 0;
            $data['require_dietary_info'] = $request->has('require_dietary_info') ? 1 : 0;
            $data['require_special_requirements'] = $request->has('require_special_requirements') ? 1 : 0;
            $data['registration_instructions'] = $request->registration_instructions;

            $data['created_by'] = auth()->id() ?? 1; // Temporary
            // Handle tags if present
            if ($request->has('tags') && is_array($request->tags)) {
                $data['tags'] = $request->tags;
            }

            $data['status'] = $request->input('status', 'draft');

            // Create event using ORM
            $event = Event::create($data);

            // Handle custom registration fields
            if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                foreach ($request->custom_fields as $index => $fieldData) {
                    EventRegistrationField::create([
                        'event_id' => $event->id,
                        'name' => $fieldData['name'],
                        'label' => $fieldData['label'],
                        'type' => $fieldData['type'],
                        'required' => isset($fieldData['required']) ? 1 : 0,
                        'options' => isset($fieldData['options']) ? json_decode($fieldData['options'], true) : null,
                        'order' => $index,
                        'placeholder' => $fieldData['placeholder'] ?? null,
                        'help_text' => $fieldData['help_text'] ?? null,
                    ]);
                }
            }

            DB::commit();

            Log::info('Event created successfully', [
                'event_id' => $event->id,
                'title' => $event->title,
                'user_id' => auth()->id() ?? 'guest',
            ]);

            // RESTful API：只返回 JSON
            // Determine redirect URL based on user role
            $showUrl = route('events.show', $event);
            if (auth()->check() && auth()->user()->hasRole('club')) {
                // If club user, redirect to club events index
                $showUrl = route('club.events.index');
            }

            return response()->json([
                        'success' => true,
                        'message' => $request->status === 'published' ? 'Event published successfully!' : 'Event saved as draft.',
                        'data' => [
                            'id' => $event->id,
                            'title' => $event->title,
                            'slug' => $event->slug ?? $event->id,
                            'status' => $event->status,
                            'show_url' => $showUrl,
                        ],
                            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Event creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id() ?? 'guest',
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to create event. Please try again.',
                        'error' => config('app.debug') ? $e->getMessage() : null,
                            ], 500);
        }
    }

    /**
     * Fetch public events via AJAX
     */
    public function fetchPublic(Request $request) {
        try {
            // Start query with published and upcoming events
            $query = Event::published()
                    ->upcoming()
                    ->with(['organizer', 'registrations']);

            // Apply category filter
            if ($request->filled('category')) {
                $query->category($request->category);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                            ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            }

            // Apply date filter
            if ($request->filled('start_date')) {
                $query->whereDate('start_time', '>=', $request->start_date);
            }

            // Apply fee type filter
            if ($request->filled('fee_type')) {
                if ($request->fee_type === 'free') {
                    $query->where('is_paid', false);
                } elseif ($request->fee_type === 'paid') {
                    $query->where('is_paid', true);
                }
            }

            // Apply sorting
            $sort = $request->input('sort', 'date_asc');
            switch ($sort) {
                case 'date_asc':
                    $query->orderBy('start_time', 'asc');
                    break;
                case 'date_desc':
                    $query->orderBy('start_time', 'desc');
                    break;
                case 'title':
                    $query->orderBy('title', 'asc');
                    break;
                default:
                    $query->orderBy('start_time', 'asc');
                    break;
            }

            // Paginate results
            $perPage = $request->input('per_page', 12);
            $events = $query->paginate($perPage);

            // Format events for response
            $formattedEvents = $events->map(function ($event) {
                $registrationsCount = $event->registrations()
                        ->where('status', ['confirmed', 'pending_payment'])
                        ->count();

                $remainingSeats = null;
                if ($event->max_participants) {
                    $remainingSeats = $event->max_participants - $registrationsCount;
                }

                return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'description_short' => Str::limit($event->description, 100),
            'venue' => $event->venue,
            'venue_short' => Str::limit($event->venue, 20),
            'category' => $event->category,
            'is_public' => $event->is_public,
            'is_paid' => $event->is_paid,
            'fee_amount' => $event->fee_amount,
            'max_participants' => $event->max_participants,
            'remaining_seats' => $remainingSeats,
            'poster_path' => $event->poster_path,
            'is_full' => $event->is_full,
            'is_registration_open' => $event->is_registration_open,
            'registration_start_time' => $event->registration_start_time->toIso8601String(), 
            'start_time_formatted' => $event->start_time->format('d M Y'),
            'start_time_time' => $event->start_time->format('h:i A'),
            'organizer_name' => $event->organizer->name ?? 'TARCampus',
                ];
            });

            return response()->json([
                        'success' => true,
                        'events' => $formattedEvents,
                        'pagination' => [
                            'current_page' => $events->currentPage(),
                            'last_page' => $events->lastPage(),
                            'per_page' => $events->perPage(),
                            'total' => $events->total(),
                            'from' => $events->firstItem(),
                            'to' => $events->lastItem(),
                        ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Fetch public events error: ' . $e->getMessage());
            return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch events.',
                        'error' => config('app.debug') ? $e->getMessage() : null,
                            ], 500);
        }
    }

    /**
     * Show the form for editing an event
     */
    public function edit(Event $event) {
        // Authorization check
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event.');
        }

        $clubs = Club::where('status', 'active')->get();

        $categories = EventCategory::values();

        return view('events.edit', compact('event', 'clubs', 'categories'));
    }

    /**
     * Update the specified event
     */
    public function update(UpdateEventRequest $request, Event $event) {
        // Authorization already checked in UpdateEventRequest

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $now = now();

            // Determine what can be updated based on event stage
            $isDraft = $event->status === 'draft';
            $isBeforeRegistration = $event->registration_start_time > $now;
            $isDuringRegistration = $event->registration_start_time <= $now && $event->registration_end_time >= $now;
            $isDuringEvent = $event->start_time <= $now && $event->end_time >= $now;
            $isPastEvent = $event->end_time < $now;

            // Handle poster upload using MediaHelper
            if ($request->hasFile('poster')) {
                try {
                    // Delete old poster and thumbnail
                    if ($event->poster_path) {
                        MediaHelper::deleteImage('event-posters/' . $event->poster_path);
                    }

                    $imageResult = MediaHelper::processImage(
                                    $request->file('poster'),
                                    'event-posters',
                                    [
                                        'max_size' => 5,
                                        'max_width' => 2000,
                                        'max_height' => 2000,
                                        'quality' => 85,
                                        'format' => 'jpg',
                                        'compress' => true,
                                        'thumbnail' => true,
                                        'thumbnail_width' => 400,
                                        'thumbnail_height' => 300,
                                    ]
                    );

                    $data['poster_path'] = $imageResult['filename'];
                    $data['poster_thumbnail_path'] = $imageResult['thumbnail_filename'] ?? null;
                } catch (\Exception $e) {
                    Log::error('Poster update failed', [
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                    ]);

                    throw new \Exception('Failed to process poster image: ' . $e->getMessage());
                }
            }

            // Handle tags
            if ($request->has('tags') && is_array($request->tags)) {
                $data['tags'] = $request->tags;
            }

            // Handle custom fields based on stage
            if (!$isDuringRegistration && !$isDuringEvent && !$isPastEvent) {
                // Can update custom fields only if NOT during/after registration
                if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                    // Delete existing custom fields
                    $event->customRegistrationFields()->delete();

                    // Create new ones
                    foreach ($request->custom_fields as $index => $fieldData) {
                        EventRegistrationField::create([
                            'event_id' => $event->id,
                            'name' => $fieldData['name'],
                            'label' => $fieldData['label'],
                            'type' => $fieldData['type'],
                            'required' => isset($fieldData['required']) ? 1 : 0,
                            'options' => isset($fieldData['options']) ? json_decode($fieldData['options'], true) : null,
                            'order' => $index,
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'help_text' => $fieldData['help_text'] ?? null,
                        ]);
                    }
                }
            }

            // Update event
            $event->update($data);

            DB::commit();

            Log::info('Event updated successfully', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'stage' => $isDraft ? 'draft' : ($isBeforeRegistration ? 'before_reg' : ($isDuringRegistration ? 'during_reg' : ($isDuringEvent ? 'during_event' : 'past'))),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Event updated successfully! ✨',
                            'redirect' => route('events.show', $event),
                ]);
            }

            return redirect()
                            ->route('events.show', $event)
                            ->with('success', 'Event updated successfully! ✨');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Event update failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => $e->getMessage(),
                                ], 500);
            }

            return back()
                            ->withInput()
                            ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified event (soft delete)
     */
    public function destroy(Event $event) {
        // Authorization check
        if (!$event->canBeEditedBy(auth()->user())) {
            if (request()->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to delete this event.',
                                ], 403);
            }
            abort(403, 'You do not have permission to delete this event.');
        }

        // Cannot delete past events
        if ($event->end_time < now()) {
            if (request()->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Past events cannot be deleted. They are kept for historical records.',
                                ], 422);
            }

            return back()->withErrors([
                        'error' => 'Past events cannot be deleted. They are kept for historical records.'
            ]);
        }

        // Cannot delete if there are confirmed registrations (except draft)
        if ($event->status !== 'draft') {
            $confirmedCount = $event->registrations()->where('status', 'confirmed')->count();

            if ($confirmedCount > 0) {
                if (request()->expectsJson()) {
                    return response()->json([
                                'success' => false,
                                'message' => "Cannot delete event with {$confirmedCount} confirmed registrations. Please cancel the event instead.",
                                    ], 422);
                }

                return back()->withErrors([
                            'error' => "Cannot delete event with {$confirmedCount} confirmed registrations. Please cancel the event instead."
                ]);
            }
        }

        try {
            // Delete poster and thumbnail using MediaHelper
            if ($event->poster_path) {
                try {
                    MediaHelper::deleteImage('event-posters/' . $event->poster_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete poster during event deletion', [
                        'event_id' => $event->id,
                        'poster_path' => $event->poster_path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Soft delete
            $event->delete();

            Log::info('Event deleted', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Event deleted successfully.',
                            'redirect' => route('events.index'),
                ]);
            }

            return redirect()
                            ->route('events.index')
                            ->with('success', 'Event deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Event deletion failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            if (request()->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Failed to delete event. Please try again.',
                                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to delete event.']);
        }
    }

    /**
     * Cancel an event
     */
    public function cancel(Request $request, Event $event) {
        // Authorization check
        if (!$event->canBeEditedBy(auth()->user())) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'You do not have permission to cancel this event.',
                                ], 403);
            }
            abort(403, 'You do not have permission to cancel this event.');
        }

        // Cannot cancel if event is already ongoing
        $now = now();
        if ($event->start_time <= $now && $event->end_time >= $now) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Cannot cancel an event that is currently in progress.',
                                ], 422);
            }

            return back()->withErrors([
                        'error' => 'Cannot cancel an event that is currently in progress.'
            ]);
        }

        // Cannot cancel past events
        if ($event->end_time < $now) {
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Cannot cancel a past event.',
                                ], 422);
            }

            return back()->withErrors([
                        'error' => 'Cannot cancel a past event.'
            ]);
        }

        // Validate cancellation reason
        $request->validate([
            'cancelled_reason' => 'required|string|min:10|max:500',
                ], [
            'cancelled_reason.required' => 'Please provide a reason for cancellation.',
            'cancelled_reason.min' => 'Cancellation reason must be at least 10 characters.',
            'cancelled_reason.max' => 'Cancellation reason cannot exceed 500 characters.',
        ]);

        try {
            DB::beginTransaction();

            // Update event status
            $event->update([
                'status' => 'cancelled',
                'cancelled_reason' => $request->cancelled_reason,
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
            ]);

            // Handle refunds for paid events
            if ($event->is_paid && $event->refund_available) {
                $confirmedRegistrations = $event->registrations()
                        ->where('status', 'confirmed')
                        ->get();

                foreach ($confirmedRegistrations as $registration) {
                    // Mark for refund processing
                    if ($registration->payment_id) {
                        $registration->update([
                            'refund_status' => 'pending',
                            'refund_requested_at' => now(),
                        ]);
                    }
                }
            }

            // 处理正在付款的（直接取消，不退款因为钱还没进）
            $event->registrations()
                    ->where('status', 'pending_payment')
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancelled_reason' => 'Event cancelled by organizer'
            ]);

            DB::commit();

            Log::info('Event cancelled', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'reason' => $request->cancelled_reason,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Event cancelled successfully. All registered participants will be notified.',
                            'redirect' => route('events.show', $event),
                ]);
            }

            return redirect()
                            ->route('events.show', $event)
                            ->with('success', 'Event cancelled successfully. All registered participants will be notified.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Event cancellation failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Failed to cancel event. Please try again.',
                                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to cancel event.']);
        }
    }

    /**
     * Publish a draft event
     */
    public function publish(Request $request, Event $event) {
        $user = auth()->user();

        // --------------------------------------------------------
        // 1. 权限检查 (Permission Check)
        // --------------------------------------------------------
        // (逻辑：Admin 可以，或者该 Event 的 Club Organizer 可以)
        if (!$event->canBeEditedBy($user)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to publish this event.');
        }

        // --------------------------------------------------------
        // 2. 状态检查 (Status Check)
        // --------------------------------------------------------
        // 只有 draft (草稿) 或 pending (待审核) 状态才能被发布
        if (!in_array($event->status, ['draft', 'pending'])) {
            return back()->with('error', 'Only draft or pending events can be published. Current status: ' . $event->status);
        }

        // --------------------------------------------------------
        // 3. 逻辑/时间检查 (Logic Validation)
        // --------------------------------------------------------
        $now = now();

        // 规则 1: Registration Start 必须在未来 (不能发布已经开始报名的活动)
        if ($event->registration_start_time <= $now) {
            return back()->with('error', 'Cannot publish: Registration start time must be in the future. Please update the dates.');
        }

        // 规则 2: Registration End 必须在 Registration Start 后面
        // (Edit 时应该已经防住了，但双重保险)
        if ($event->registration_end_time <= $event->registration_start_time) {
            return back()->with('error', 'Invalid dates: Registration end time must be after start time.');
        }

        // 规则 3: Event Start 必须在 Registration End 后面
        // (你的核心规则：必须等报名彻底结束，活动才开始)
        if ($event->start_time <= $event->registration_end_time) {
            return back()->with('error', 'Invalid dates: Event cannot start before registration ends.');
        }

        // 规则 4: Event End 必须在 Event Start 后面
        if ($event->end_time <= $event->start_time) {
            return back()->with('error', 'Invalid dates: Event end time must be after start time.');
        }

        // C. 检查是否有海报，因为首页没海报很难看
//        if (empty($event->poster_path)) {
//            // 如果你的 UI 允许没海报，这行可以注释掉
//            return back()->with('error', 'Please upload a poster before publishing.');
//        }
        // --------------------------------------------------------
        // 4. 执行发布 (Action)
        // --------------------------------------------------------
        try {
            // 直接变 Published，跳过审核
            $event->update([
                'status' => 'published',
                'published_at' => $now, // 记录这一刻的时间
            ]);

            // 记录日志
            \Log::info('Event published', ['event_id' => $event->id, 'user_id' => $user->id]);

            return redirect()->route('events.show', $event)
                            ->with('success', 'Event published successfully! Registration is now open.');
        } catch (\Exception $e) {
            \Log::error('Event publish failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to publish event. Please try again.');
        }
    }

    /**
     * AJAX validation endpoint
     */
    public function validateField(Request $request) {
        $field = $request->input('field');
        $value = $request->input('value');

        // 跨字段规则用到的其它值
        $data = $request->input('all', []);
        $data[$field] = $value;

        // 规则：手动 copy 自 StoreEventRequest::rules()
        $rules = [
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:20|max:5000',
            'venue' => 'required|string|max:255',
            'category' => [
                'required',
                new Enum(EventCategory::class),
            ],
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'registration_start_time' => 'required|date|before:start_time',
            'registration_end_time' => 'required|date|after:registration_start_time|before:start_time',
            'max_participants' => 'nullable|integer|min:1|max:10000',
            'fee_amount' => 'required_if:is_paid,true|nullable|numeric|min:0|max:10000',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|regex:/^[0-9\+\-\(\)\s]+$/',
            'location_map_url' => 'nullable|url|max:500',
        ];

        // 自定义 message：跟 StoreEventRequest::messages() 对齐
        $messages = [
            'title.required' => 'Event title is required.',
            'title.min' => 'Event title must be at least 5 characters.',
            'description.required' => 'Event description is required.',
            'description.min' => 'Event description must be at least 20 characters.',
            'start_time.after' => 'Event must start in the future.',
            'end_time.after' => 'Event end time must be after start time.',
            'registration_start_time.before' => 'Registration must start before the event begins.',
            'registration_end_time.before' => 'Registration must close before event starts.',
            'registration_end_time.after' => 'Registration close time must be after open time.',
            'fee_amount.required_if' => 'Fee amount is required for paid events.',
            'poster.image' => 'Poster must be an image file.',
            'poster.max' => 'Poster size must not exceed 5MB.',
            'contact_phone.regex' => 'Please enter a valid phone number.',
            'contact_email.required' => 'Contact email is required.',
            'venue.required' => 'Event venue is required.',
        ];

        if (!isset($rules[$field])) {
            return response()->json(['valid' => true]);
        }

        // 只验证当前字段
        $validator = \Validator::make(
                        $data,
                        [$field => $rules[$field]],
                        $messages
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
