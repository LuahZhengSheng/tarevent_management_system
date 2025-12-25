<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Required for UUID validation

class ClubEventController extends Controller
{
    public function index(Request $request)
    {
        // ==========================================
        // 1. Mandatory Request Check & Validation
        // ==========================================
        
        // 1.1 Check parameter existence
        // Either 'requestID' OR 'timestamp' must be provided for traceability
        if (!$request->has('requestID') && !$request->has('timestamp')) {
            return response()->json([
                'status'    => 'E', // Error: Missing parameters
                'message'   => 'Mandatory Requirement Missing: requestID or timestamp is required',
                'timeStamp' => now()->toDateTimeString(),
            ], 400);
        }

        // Determine the primary identifier for logging
        // Use requestID if available, otherwise fallback to prefixed timestamp
        $requestId = $request->input('requestID') ?? ('TIME-' . $request->input('timestamp'));

        // 1.2 Check Format (Defensive Programming)
        
        // A. Validate requestID (if present)
        if ($request->has('requestID') && !Str::isUuid($request->input('requestID'))) {
            return response()->json([
                'status'    => 'E', // Error: Invalid format
                'message'   => 'Invalid Format: requestID must be a valid UUID',
                'timeStamp' => now()->toDateTimeString(),
            ], 400);
        }

        // B. Validate timestamp (if present)
        if ($request->has('timestamp')) {
            // Strict Regex for YYYY-MM-DD HH:mm:ss
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $request->input('timestamp'))) {
                return response()->json([
                    'status'    => 'E', // Error: Invalid format
                    'message'   => 'Invalid Format: timestamp must be YYYY-MM-DD HH:mm:ss',
                    'timeStamp' => now()->toDateTimeString(),
                ], 400);
            }
        }

        // ==========================================
        // 2. Auth Context (Guaranteed by Middleware)
        // ==========================================
        $user = Auth::user(); 

        // 3. Permission Check
        if ($user->role !== 'club') { 
            // Log the permission denial
            Log::warning("API Permission Denied: User {$user->id} is not a club.", [
                'request_id' => $requestId, // Traceability key
                'user_id'    => $user->id
            ]);

             return response()->json([
                'status'    => 'F', // Fail: Permission denied
                'message'   => 'Forbidden: Only clubs allowed',
                'timeStamp' => now()->toDateTimeString(),
            ], 403);
        }

        // ==========================================
        // 4. Log the Valid Request (Traceability)
        // ==========================================
        Log::info("API Request Processing: Fetching club dashboard stats.", [
            'request_id' => $requestId,
            'user_id'    => $user->id,
            'timestamp'  => $request->input('timestamp'),
            'ip'         => $request->ip()
        ]);

        // ==========================================
        // 5. Business Logic
        // ==========================================
        try {
            // Initialize default stats to ensure structure consistency
            $defaultStats = [
                'draft'     => 0,
                'published' => 0,
                'cancelled' => 0,
                'completed' => 0,
            ];

            // Optimized Query: Group By for efficient counting
            $dbStats = Event::where('organizer_id', $user->id) // 🔒 Security: Only own events
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status') // Returns array like ['published' => 5]
                ->toArray();

            // Merge DB results into default stats (DB overwrites defaults)
            $finalStats = array_merge($defaultStats, $dbStats);
            $totalEvents = array_sum($finalStats);

            // Log success
            Log::info("API Success: Returned stats for club {$user->id}.", [
                'request_id' => $requestId,
                'user_id'    => $user->id,
                'total_events' => $totalEvents
            ]);

            // ==========================================
            // 6. Successful Response (S)
            // ==========================================
            return response()->json([
                'status'    => 'S', // Success
                'timeStamp' => now()->toDateTimeString(), 
                'message'   => 'Data retrieved successfully',
                'data'      => [
                    'counts' => $finalStats, // { "draft": 0, "published": 5, ... }
                    'total'  => $totalEvents
                ],
            ], 200);

        } catch (\Exception $e) {
            // ==========================================
            // 7. System Error Response (E)
            // ==========================================
            // Log the actual system error linked to the Request ID
            Log::error('API Internal Error: ' . $e->getMessage(), [
                'request_id' => $requestId,
                'user_id'    => $user->id,
                'trace'      => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status'    => 'E', // Error: Internal Server Error
                'message'   => 'Internal Server Error',
                'timeStamp' => now()->toDateTimeString(),
            ], 500);
        }
    }
}
