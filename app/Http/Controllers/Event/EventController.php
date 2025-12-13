<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Club;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Display a listing of events (User Site)
     */
    public function index(Request $request)
    {
        $query = Event::published()
                     ->upcoming()
                     ->with(['organizer', 'registrations']);

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by search term
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
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

        $categories = Event::published()
                          ->distinct()
                          ->pluck('category')
                          ->filter();

        return view('events.index', compact('events', 'categories'));
    }

    /**
     * Display event details
     */
    public function show(Event $event)
    {
        // Authorization check
        if ($event->status !== 'published' && !$event->canBeEditedBy(auth()->user())) {
            abort(403, 'This event is not available for viewing.');
        }

        $event->load(['organizer', 'registrations', 'creator']);
        
        // Check if current user is registered
        $isRegistered = false;
        $userRegistration = null;
        
        if (auth()->check()) {
            $userRegistration = $event->registrations()
                                     ->where('user_id', auth()->id())
                                     ->first();
            $isRegistered = $userRegistration !== null;
        }

        return view('events.show', compact('event', 'isRegistered', 'userRegistration'));
    }

    /**
     * Show the form for creating a new event (Club Admin Only)
     */
    public function create()
    {
        // Authorization: Must be club admin
//        if (!auth()->user()->hasRole('club')) {
//            abort(403, 'Only club administrators can create events.');
//        }

        $clubs = Club::where('status', 'active')->get();
        $categories = ['Academic', 'Sports', 'Cultural', 'Workshop', 'Social', 'Career', 'Technology'];

        return view('events.create', compact('clubs', 'categories'));
    }

    /**
     * Store a newly created event
     */
    public function store(StoreEventRequest $request)
    {
        // Authorization check
        if (!auth()->user()->hasRole('club')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Handle poster upload
            if ($request->hasFile('poster')) {
                $posterPath = $request->file('poster')->store('event-posters', 'public');
                $data['poster_path'] = $posterPath;
            }

            // Set organizer info
            $data['organizer_id'] = $request->club_id ?? auth()->user()->club_id;
            $data['organizer_type'] = 'club';

            // Create event using ORM (prepared statement automatically)
            $event = Event::create($data);

            DB::commit();

            Log::info('Event created successfully', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event created successfully!',
                    'redirect' => route('events.show', $event),
                ]);
            }

            return redirect()
                ->route('events.show', $event)
                ->with('success', 'Event created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Event creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create event. Please try again.',
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create event. Please try again.']);
        }
    }

    /**
     * Show the form for editing an event
     */
    public function edit(Event $event)
    {
        // Authorization check
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event.');
        }

        $clubs = Club::where('status', 'active')->get();
        $categories = ['Academic', 'Sports', 'Cultural', 'Workshop', 'Social', 'Career', 'Technology'];

        return view('events.edit', compact('event', 'clubs', 'categories'));
    }

    /**
     * Update the specified event
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        // Authorization check
        if (!$event->canBeEditedBy(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle poster upload
            if ($request->hasFile('poster')) {
                // Delete old poster
                if ($event->poster_path) {
                    Storage::disk('public')->delete($event->poster_path);
                }
                
                $posterPath = $request->file('poster')->store('event-posters', 'public');
                $data['poster_path'] = $posterPath;
            }

            $event->update($data);

            DB::commit();

            Log::info('Event updated successfully', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Event updated successfully!',
                ]);
            }

            return redirect()
                ->route('events.show', $event)
                ->with('success', 'Event updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Event update failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update event.',
                ], 500);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update event.']);
        }
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        // Authorization check
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this event.');
        }

        try {
            // Soft delete
            $event->delete();

            Log::info('Event deleted', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->route('events.index')
                ->with('success', 'Event deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Event deletion failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to delete event.']);
        }
    }

    /**
     * AJAX validation endpoint
     */
    public function validateField(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $rules = [
            'title' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'registration_start_time' => 'required|date',
            'registration_end_time' => 'required|date|after:registration_start_time|before:start_time',
            'max_participants' => 'nullable|integer|min:1',
            'fee_amount' => 'nullable|numeric|min:0',
        ];

        if (!isset($rules[$field])) {
            return response()->json(['valid' => true]);
        }

        $validator = \Validator::make(
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