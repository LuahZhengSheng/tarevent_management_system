<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Required for UUID validation

class UserEventController extends Controller
{
    public function index(Request $request)
    {
        // ==========================================
        // 1. Mandatory Request Check & Validation
        // ==========================================
        
        // 1.1 Check parameter existence
        if (!$request->has('requestID')) {
            return response()->json([
                'status'    => 'E', // Error: Missing parameters
                'message'   => 'Mandatory Requirement Missing: requestID',
                'timeStamp' => now()->toDateTimeString(),
            ], 400);
        }

        $requestId = $request->input('requestID');

        // 1.2 Check UUID Format (Defensive Programming)
        // Ensures the frontend is sending a valid UUID v4 string
        if (!Str::isUuid($requestId)) {
            return response()->json([
                'status'    => 'E', // Error: Invalid format
                'message'   => 'Invalid Format: requestID must be a valid UUID',
                'timeStamp' => now()->toDateTimeString(),
            ], 400);
        }

        // ==========================================
        // 2. Auth Context (Guaranteed by Middleware)
        // ==========================================
        // Auth::check() is not needed because the middleware already indicates that the user is logged in
        $user = Auth::user(); 

        // 3. Permission Check
        if ($user->role !== 'student') { 
            // Log the permission denial
            Log::warning("API Permission Denied: User {$user->id} is not a student.", [
                'request_id' => $requestId,
                'user_id' => $user->id
            ]);

             return response()->json([
                'status'    => 'F', // Fail: Permission denied
                'message'   => 'Forbidden: Only students allowed',
                'timeStamp' => now()->toDateTimeString(),
            ], 403);
        }

        // ==========================================
        // 4. Log the Valid Request (Traceability)
        // ==========================================
        // This links the Request ID to the User ID in your system logs
        Log::info("API Request Processing: Fetching joined events.", [
            'request_id' => $requestId,
            'user_id'    => $user->id,
            'timestamp'  => $request->input('timeStamp'),
            'ip'         => $request->ip()
        ]);

        // ==========================================
        // 5. Business Logic
        // ==========================================
        try {
            $registrations = EventRegistration::with('event')
                ->where('user_id', $user->id)
                ->where('status', 'confirmed')
                ->whereHas('event')
                ->latest()
                ->get();

            $formattedEvents = $registrations->map(function ($reg) {
                return [
                    'event_id'      => $reg->event->id,
                    'event_name'    => $reg->event->title, 
                    'register_date' => $reg->created_at->toDateTimeString(),
                    'status'        => $reg->status,
                ];
            })
            ->unique('event_id')
            ->values();

            // Log success
            Log::info("API Success: Returned {$formattedEvents->count()} events.", [
                'request_id' => $requestId,
                'user_id' => $user->id
            ]);

            // ==========================================
            // 6. Successful Response (S)
            // ==========================================
            return response()->json([
                'status'    => 'S', // Success
                'timeStamp' => now()->toDateTimeString(), 
                'message'   => 'Data retrieved successfully',
                'data'      => [
                    'totalEvents' => $formattedEvents->count(), 
                    'events'      => $formattedEvents,
                ],
            ], 200);

        } catch (\Exception $e) {
            // ==========================================
            // 7. System Error Response (E)
            // ==========================================
            // Log the actual system error linked to the Request ID
            Log::error('API Internal Error: ' . $e->getMessage(), [
                'request_id' => $requestId,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status'    => 'E', // Error: Internal Server Error
                'message'   => 'Internal Server Error',
                'timeStamp' => now()->toDateTimeString(),
            ], 500);
        }
    }
}
