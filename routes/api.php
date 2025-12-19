<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClubApiController;

// For testing: Using 'auth' middleware with web session support
// Note: API routes don't have session middleware by default
// For production, use 'auth:sanctum' with proper token authentication
Route::middleware(['web', 'auth'])->group(function () {
    // Club API Routes
    Route::prefix('clubs')->group(function () {
        // Create club
        Route::post('/', [ClubApiController::class, 'store']);

        // Join request routes
        Route::prefix('{club}')->group(function () {
            // Request to join a club
            Route::post('/join', [ClubApiController::class, 'requestJoin']);

            // Approve/Reject join requests
            Route::prefix('join/{user}')->group(function () {
                Route::post('/approve', [ClubApiController::class, 'approveJoin']);
                Route::post('/reject', [ClubApiController::class, 'rejectJoin']);
            });
        });
    });
});

