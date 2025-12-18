<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClubApiController;
use App\Http\Controllers\Api\ClubUserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Club API Routes (requires authentication)
Route::middleware(['auth:sanctum'])->group(function () {
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

// Club User API Routes (v1)
Route::prefix('v1')->group(function () {
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
