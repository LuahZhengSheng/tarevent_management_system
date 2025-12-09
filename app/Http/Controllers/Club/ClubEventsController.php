<?php

namespace App\Http\Controllers\Club;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClubEventsController extends Controller
{
    /**
     * Display club's events management page
     */
    public function index()
    {
        // Authorization: Must be club admin
        // if (!auth()->user()->hasRole('club')) {
        //     abort(403, 'Only club administrators can access this page.');
        // }

        return view('clubs.events.index');
    }

    /**
     * Fetch events via AJAX
     */
    public function fetch(Request $request)
    {
        // Authorization check
        // if (!auth()->user()->hasRole('club')) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthorized access.',
        //     ], 403);
        // }

        try {
            // Get club's organizer ID
            // $organizerId = auth()->user()->club_id;
            $organizerId = 1; // Temporary for testing

            // Start query - only show events created by this club
            $query = Event::where('organizer_id', $organizerId)
                          ->where('organizer_type', 'club')
                          ->with(['registrations']);

            // Apply time-based filter
            $timeFilter = $request->input('timeFilter', 'all');
            $this->applyTimeFilter($query, $timeFilter);

            // Apply status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Apply category filter
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Apply fee type filter
            if ($request->filled('feeType')) {
                if ($request->feeType === 'free') {
                    $query->where('is_paid', false);
                } elseif ($request->feeType === 'paid') {
                    $query->where('is_paid', true);
                }
            }

            // Apply visibility filter
            if ($request->filled('visibility')) {
                $query->where('is_public', $request->visibility === 'public' ? true : false);
            }

            // Apply registration status filter
            if ($request->filled('registration')) {
                $this->applyRegistrationFilter($query, $request->registration);
            }

            // Apply date range filter
            if ($request->filled('dateFrom')) {
                $query->whereDate('start_time', '>=', $request->dateFrom);
            }

            if ($request->filled('dateTo')) {
                $query->whereDate('start_time', '<=', $request->dateTo);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%')
                      ->orWhere('venue', 'like', '%' . $searchTerm . '%');
                });
            }

            // Apply sorting
            $this->applySorting($query, $request->input('sort', 'date_desc'));

            // Get statistics before pagination
            $stats = $this->getStatistics($organizerId);

            // Paginate results
            $perPage = $request->input('per_page', 12);
            $events = $query->paginate($perPage);

            // Format events for response
            $formattedEvents = $events->map(function ($event) {
                return $this->formatEvent($event);
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
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            \Log::error('Fetch club events error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch events.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Apply time-based filter
     */
    protected function applyTimeFilter($query, $timeFilter)
    {
        $now = now();

        switch ($timeFilter) {
            case 'upcoming':
                $query->where('start_time', '>', $now)
                      ->where('status', 'published');
                break;

            case 'ongoing':
                $query->where('start_time', '<=', $now)
                      ->where('end_time', '>=', $now)
                      ->where('status', 'published');
                break;

            case 'past':
                $query->where('end_time', '<', $now)
                      ->where('status', 'published');
                break;

            case 'draft':
                $query->where('status', 'draft');
                break;

            case 'all':
            default:
                // No additional filter
                break;
        }
    }

    /**
     * Apply registration status filter
     */
    protected function applyRegistrationFilter($query, $registrationStatus)
    {
        $now = now();

        switch ($registrationStatus) {
            case 'open':
                $query->where('registration_start_time', '<=', $now)
                      ->where('registration_end_time', '>=', $now)
                      ->where('status', 'published');
                break;

            case 'closed':
                $query->where(function ($q) use ($now) {
                    $q->where('registration_end_time', '<', $now)
                      ->orWhere('registration_start_time', '>', $now);
                })
                ->where('status', 'published');
                break;

            case 'full':
                $query->whereNotNull('max_participants')
                      ->whereHas('registrations', function ($q) {
                          // This needs more complex logic with subquery
                      })
                      ->where('status', 'published');
                break;
        }
    }

    /**
     * Apply sorting
     */
    protected function applySorting($query, $sort)
    {
        switch ($sort) {
            case 'date_asc':
                $query->orderBy('start_time', 'asc');
                break;

            case 'date_desc':
                $query->orderBy('start_time', 'desc');
                break;

            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;

            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;

            case 'registrations_desc':
                $query->withCount('registrations')
                      ->orderBy('registrations_count', 'desc');
                break;

            case 'registrations_asc':
                $query->withCount('registrations')
                      ->orderBy('registrations_count', 'asc');
                break;

            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;

            case 'updated_desc':
                $query->orderBy('updated_at', 'desc');
                break;

            default:
                $query->orderBy('start_time', 'desc');
                break;
        }
    }

    /**
     * Get statistics for the club
     */
    protected function getStatistics($organizerId)
    {
        $now = now();

        $total = Event::where('organizer_id', $organizerId)
                     ->where('organizer_type', 'club')
                     ->count();

        $upcoming = Event::where('organizer_id', $organizerId)
                        ->where('organizer_type', 'club')
                        ->where('start_time', '>', $now)
                        ->where('status', 'published')
                        ->count();

        $ongoing = Event::where('organizer_id', $organizerId)
                       ->where('organizer_type', 'club')
                       ->where('start_time', '<=', $now)
                       ->where('end_time', '>=', $now)
                       ->where('status', 'published')
                       ->count();

        $draft = Event::where('organizer_id', $organizerId)
                     ->where('organizer_type', 'club')
                     ->where('status', 'draft')
                     ->count();

        return [
            'total' => $total,
            'upcoming' => $upcoming,
            'ongoing' => $ongoing,
            'draft' => $draft,
        ];
    }

    /**
     * Format event for JSON response
     */
    protected function formatEvent($event)
    {
        $registrationsCount = $event->registrations()
                                   ->where('status', 'confirmed')
                                   ->count();

        $remainingSeats = null;
        if ($event->max_participants) {
            $remainingSeats = $event->max_participants - $registrationsCount;
        }

        // Determine registration status
        $registrationStatus = 'Closed';
        if ($event->status === 'draft') {
            $registrationStatus = 'Draft';
        } elseif ($event->is_registration_open) {
            if ($remainingSeats === 0) {
                $registrationStatus = 'Full';
            } else {
                $registrationStatus = 'Open';
            }
        }

        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'description_short' => Str::limit($event->description, 100),
            'venue' => $event->venue,
            'venue_short' => Str::limit($event->venue, 30),
            'category' => $event->category,
            'status' => $event->status,
            'is_paid' => $event->is_paid,
            'is_public' => $event->is_public,
            'fee_amount' => $event->fee_amount,
            'max_participants' => $event->max_participants,
            'remaining_seats' => $remainingSeats,
            'poster_path' => $event->poster_path,
            'start_time' => $event->start_time->toISOString(),
            'start_time_formatted' => $event->start_time->format('d M Y'),
            'start_time_time' => $event->start_time->format('h:i A'),
            'end_time' => $event->end_time->toISOString(),
            'registration_status' => $registrationStatus,
            'registrations_count' => $registrationsCount,
            'views_count' => $event->views_count ?? 0,
            'created_at' => $event->created_at->toISOString(),
            'updated_at' => $event->updated_at->toISOString(),
            'updated_at_human' => $event->updated_at->diffForHumans(),
        ];
    }
}