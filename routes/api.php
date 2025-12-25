<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClubApiController;
use App\Http\Controllers\Api\ClubUserController;
use App\Http\Controllers\Api\ClubPostFeedController;
use App\Http\Controllers\Api\ForumUserStatsController;
use App\Http\Controllers\Api\AuthTokenController;


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
    // ======================
    // Club User APIs
    // ======================
    // Create club user
    Route::post('/club-users', [ClubUserController::class, 'store'])
            ->name('api.v1.club-users.store');

    // Get club user
    Route::get('/club-users/{user}', [ClubUserController::class, 'show'])
            ->name('api.v1.club-users.show');

    // List club users
    Route::get('/club-users', [ClubUserController::class, 'index'])
            ->name('api.v1.club-users.index');

    // ======================
    // Forum User Stats API
    // ======================
    // GET /api/v1/forum/user-stats?userId=1&requestId=REQ-20241223-0001
//    Route::get('/forum/user-stats', [ForumUserStatsController::class, 'getUserForumStats'])
//            ->name('api.v1.forum.user-stats');
//
//    // Club Posts Feed API (member only)
//    Route::get('/clubs/{club}/posts', [ClubPostFeedController::class, 'index'])
//            ->name('api.v1.clubs.posts');
});

// Internal API route for club user creation (no auth required for internal calls)
// This route is used by ClubFacade for internal club user creation
Route::prefix('v1/internal')->group(function () {
    Route::post('/club-users', [ClubUserController::class, 'store'])
            ->name('api.v1.internal.club-users.store');
});

//Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//    Route::get('/forum/user-stats', [ForumUserStatsController::class, 'getUserForumStats'])
//        ->name('api.v1.forum.user-stats');
//
//    Route::get('/clubs/{club}/posts', [ClubPostFeedController::class, 'index'])
//        ->name('api.v1.clubs.posts');
//});


Route::prefix('v1')->group(function () {

    // 1) 外部系统换 token（不走 auth:sanctum）
    Route::post('/auth/token', [AuthTokenController::class, 'issueToken'])
            ->name('api.v1.auth.token');

    // 2) 其余 API：必须带 Bearer token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthTokenController::class, 'logout'])
                ->name('api.v1.auth.logout');

        Route::get('/forum/user-stats', [ForumUserStatsController::class, 'getUserForumStats'])
                ->name('api.v1.forum.user-stats');

        Route::get('/clubs/{club}/posts', [ClubPostFeedController::class, 'index'])
                ->name('api.v1.clubs.posts');
    });
});
