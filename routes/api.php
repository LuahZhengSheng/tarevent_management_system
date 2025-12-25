<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClubApiController;
use App\Http\Controllers\Api\ClubUserController;
use App\Http\Controllers\Api\UserEventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Club API Routes (requires authentication)
// Note: Using ['web','auth'] instead of 'auth:sanctum' for development/testing
// This allows web session authentication to work with API routes
// For production, consider using 'auth:sanctum' with proper token authentication
Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('clubs')->group(function () {
        // Get all available clubs with join status
        Route::get('/available', [ClubApiController::class, 'getAvailableClubs']);

        // Create club
        Route::post('/', [ClubApiController::class, 'store']);

        // Update club
        Route::put('/{club}', [ClubApiController::class, 'update']);

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

    // Get all clubs for a user
    Route::get('/users/{user}/clubs', [ClubApiController::class, 'getUserClubs']);
});

// Club User API Routes (v1)
// Note: Using ['web','auth'] for development/testing to allow web session authentication
Route::middleware(['web', 'auth'])->prefix('v1')->group(function () {
    // Create club user
    Route::post('/club-users', [ClubUserController::class, 'store'])
        ->name('api.v1.club-users.store');
    
    // Get club user
    Route::get('/club-users/{user}', [ClubUserController::class, 'show'])
        ->name('api.v1.club-users.show');
    
    // List club users
    Route::get('/club-users', [ClubUserController::class, 'index'])
        ->name('api.v1.club-users.index');
});

// Internal API route for club user creation (no auth required for internal calls)
// This route is used by ClubFacade for internal club user creation
Route::prefix('v1/internal')->group(function () {
    Route::post('/club-users', [ClubUserController::class, 'store'])
        ->name('api.v1.internal.club-users.store');
});

// ==============================================================
// User Events API
// ==============================================================

//Route::middleware(['web'])->group(function () {
//    Route::get('/user/joined-events', [UserEventController::class, 'index']);
//});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/joined-events', [UserEventController::class, 'index']);
});