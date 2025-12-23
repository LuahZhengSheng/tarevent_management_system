<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Event\EventRegistrationController;
use App\Http\Controllers\Club\ClubEventsController;
use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\MyPostController;
use App\Http\Controllers\Forum\CommentController;
use App\Http\Controllers\Forum\LikeController;
use App\Http\Controllers\Forum\PostSaveController;
use App\Http\Controllers\Forum\PostReportController;
//use App\Http\Controllers\Club\ClubController;
//use App\Http\Controllers\User\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (app()->environment('local')) {
    Route::get('/__debug-which-app__', function () {
        return 'APP SIGN: TAREVENT ' . base_path();
    });
}

if (app()->environment('local')) {
    Route::get('/debug-club-middleware', function () {
        $mw = app(\App\Http\Middleware\CheckClubRole::class);
        dd('resolved', get_class($mw));
    });
}

if (app()->environment('local')) {
    Route::get('/dev-login/{id}', function ($id) {
        $user = User::findOrFail($id);
        Auth::login($user);      // 等价 auth()->login($user);
        return redirect()->route('home');
    });
}

Route::get('/dev-logout', function () {
    Auth::logout();                  // 清掉当前用户
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home'); // 回到 /
})->name('dev-logout');

if (app()->environment('local')) {
    Route::get('/whoami', function () {
        dump(auth()->check(), auth()->user());
    });
}

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
 */

// Public Routes
Route::redirect('/', '/forums')->name('home');

Route::get('/login', function () {
    // 简单占位，可以改成你的登录页面
    return view('welcome'); // 确保有 resources/views/auth/login.blade.php
})->name('login');

// Authentication Routes
//Route::middleware('guest')->group(function () {
//    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
//    Route::post('/login', [LoginController::class, 'login']);
//    Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register');
//    Route::post('/register', [UserController::class, 'register']);
//});
//
//Route::post('/logout', [LoginController::class, 'logout'])
//    ->middleware('auth')
//    ->name('logout');
// Public Event Browsing (No Auth Required)
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/fetch', [EventController::class, 'fetchPublic'])->name('events.fetch');
//Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
//// Public Club Browsing
//Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
//Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');
//
//// Public Forum Browsing
//Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
//Route::get('/forum/{post}', [ForumController::class, 'show'])->name('forum.show');

/*
  |--------------------------------------------------------------------------
  | Authenticated Routes
  |--------------------------------------------------------------------------
 */
Route::middleware(['auth', 'user'])->group(function () {
//    
//    // User Profile Management
//    Route::prefix('profile')->name('profile.')->group(function () {
//        Route::get('/', [UserController::class, 'show'])->name('show');
//        Route::get('/edit', [UserController::class, 'edit'])->name('edit');
//        Route::put('/update', [UserController::class, 'update'])->name('update');
//        Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('change-password');
//        Route::put('/change-password', [UserController::class, 'changePassword'])->name('update-password');
//    });
    // Event Registration Routes (For Students)
    Route::prefix('events/{event}')->name('events.register.')->group(function () {
        Route::get('/register', [EventRegistrationController::class, 'create'])->name('create');
        Route::post('/register', [EventRegistrationController::class, 'store'])->name('store');
    });

    // AJAX Validation for Registration
    Route::post('/events/register/validate-field', [EventRegistrationController::class, 'validateField'])
            ->name('events.register.validate');

    // Payment Routes
    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/{registration}/payment', [EventRegistrationController::class, 'payment'])
                ->name('payment');
        Route::post('/{registration}/pay', [EventRegistrationController::class, 'pay'])
                ->name('pay');
    });

    // My Events (User's registered events)
    Route::get('/my-events', [EventRegistrationController::class, 'myEvents'])
            ->name('events.my');

    // Cancel Registration
    Route::delete('/registrations/{registration}', [EventRegistrationController::class, 'destroy'])
            ->name('registrations.cancel');

//    // Forum Interactions (Authenticated Users)
//    Route::prefix('forum')->name('forum.')->group(function () {
//        Route::post('/posts', [ForumController::class, 'store'])->name('posts.store');
//        Route::put('/posts/{post}', [ForumController::class, 'update'])->name('posts.update');
//        Route::delete('/posts/{post}', [ForumController::class, 'destroy'])->name('posts.destroy');
//        
//        Route::post('/posts/{post}/comments', [ForumController::class, 'storeComment'])->name('comments.store');
//        Route::delete('/comments/{comment}', [ForumController::class, 'destroyComment'])->name('comments.destroy');
//    });
});

/*
  |--------------------------------------------------------------------------
  | Forum Routes
  |--------------------------------------------------------------------------
 */

Route::prefix('forums')->name('forums.')->group(function () {

    // Public (view only)
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

    // Tags: search public, request auth
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/search', [PostController::class, 'searchTags'])->name('search');
        Route::post('/request', [PostController::class, 'requestTag'])
                ->middleware('auth')
                ->name('request');
    });

    // Auth required
    Route::middleware(['auth', 'check.active.user'])->group(function () {

        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/{post:slug}/edit', [PostController::class, 'edit'])->name('edit');
            Route::put('/{post:slug}', [PostController::class, 'update'])->name('update');
            Route::delete('/{post:slug}', [PostController::class, 'destroy'])->name('destroy');
            Route::post('/{post:slug}/toggle-status', [PostController::class, 'toggleStatus'])->name('toggle-status');

            // Comment / Reply
            Route::post('/{post:slug}/comments', [CommentController::class, 'store'])->name('comments.store');

            // 按页获取 replies，用于“View X reply”
            Route::get('/{post:slug}/comments/{comment}/replies', [CommentController::class, 'listReplies'])
                    ->name('comments.replies');

            // 顶层评论排序（AJAX）
            Route::get('/{post:slug}/comments/top-level', [CommentController::class, 'listTopLevel'])
                    ->name('comments.top-level');

            // Post Like
            Route::post('/{post:slug}/like', [LikeController::class, 'toggle'])->name('like.toggle');
            Route::get('/{post:slug}/likes', [LikeController::class, 'users'])->name('likes.users');

            // Save
            Route::post('/{post:slug}/save', [PostSaveController::class, 'toggle'])->name('save.toggle');

            // Report
            Route::post('/{post:slug}/report', [PostReportController::class, 'store'])->name('report.store');
        });

        // Comment delete
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        // Comment update
        Route::put('/comments/{comment}', [CommentController::class, 'update'])
                ->name('comments.update');

        // Comment Like
        Route::post('/comments/{comment}/like', [LikeController::class, 'toggleComment'])
                ->name('comments.like');

        Route::get('/create', [PostController::class, 'create'])->name('create');
        Route::post('/', [PostController::class, 'store'])->name('store');

        // My posts
        Route::get('/my-posts', [MyPostController::class, 'index'])->name('my-posts');
        Route::post('/my-posts/quick-delete', [MyPostController::class, 'quickDelete'])->name('my-posts.quick-delete');
    });
});

/*
  |--------------------------------------------------------------------------
  | Club Admin Routes
  |--------------------------------------------------------------------------
  | Only accessible by users with 'club' role
 */
Route::middleware(['auth', 'club'])->prefix('events')->name('events.')->group(function () {
//Route::prefix('events')->name('events.')->group(function () {
    // Event Management (Create, Edit, Delete)
    Route::post('/', [EventController::class, 'store'])->name('store');
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::get('/{event}/edit', [EventController::class, 'edit'])->name('edit');
    Route::put('/{event}', [EventController::class, 'update'])->name('update');
    Route::delete('/{event}', [EventController::class, 'destroy'])->name('destroy');

    // Event Status Management
    Route::post('/{event}/publish', [EventController::class, 'publish'])->name('publish');
    Route::post('/{event}/cancel', [EventController::class, 'cancel'])->name('cancel');

    // AJAX Field Validation
    Route::post('/validate-field', [EventController::class, 'validateField'])
            ->name('validate-field');

    // Event Registrations Management
    Route::get('/{event}/registrations', [EventRegistrationController::class, 'index'])
            ->name('registrations.index');
    Route::get('/{event}/registrations/export', [EventRegistrationController::class, 'export'])
            ->name('registrations.export');
});

/*
  |--------------------------------------------------------------------------
  | Admin Routes
  |--------------------------------------------------------------------------
  | Only accessible by users with 'admin' role
 */
//Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // User Management
//    Route::prefix('users')->name('users.')->group(function () {
//        Route::get('/', [UserController::class, 'index'])->name('index');
//        Route::get('/{user}', [UserController::class, 'adminShow'])->name('show');
//        Route::put('/{user}/suspend', [UserController::class, 'suspend'])->name('suspend');
//        Route::put('/{user}/activate', [UserController::class, 'activate'])->name('activate');
//        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
//    });
    // Admin Management
//    Route::prefix('admins')->name('admins.')->group(function () {
//        Route::get('/', [UserController::class, 'adminsIndex'])->name('index');
//        Route::get('/create', [UserController::class, 'createAdmin'])->name('create');
//        Route::post('/', [UserController::class, 'storeAdmin'])->name('store');
//        Route::delete('/{user}', [UserController::class, 'destroyAdmin'])->name('destroy');
//    });
    // All Events Management
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [EventController::class, 'adminIndex'])->name('index');
        Route::post('/{event}/approve', [EventController::class, 'approve'])->name('approve');
        Route::post('/{event}/reject', [EventController::class, 'reject'])->name('reject');
    });

    // Club Management
//    Route::prefix('clubs')->name('clubs.')->group(function () {
//        Route::get('/', [ClubController::class, 'adminIndex'])->name('index');
//        Route::get('/create', [ClubController::class, 'create'])->name('create');
//        Route::post('/', [ClubController::class, 'store'])->name('store');
//        Route::get('/{club}/edit', [ClubController::class, 'edit'])->name('edit');
//        Route::put('/{club}', [ClubController::class, 'update'])->name('update');
//        Route::delete('/{club}', [ClubController::class, 'destroy'])->name('destroy');
//    });
    // Reports & Analytics
//    Route::prefix('reports')->name('reports.')->group(function () {
//        Route::get('/events', [EventController::class, 'eventsReport'])->name('events');
//        Route::get('/registrations', [EventRegistrationController::class, 'registrationsReport'])->name('registrations');
//        Route::get('/payments', [EventRegistrationController::class, 'paymentsReport'])->name('payments');
//    });
});

/*
  |--------------------------------------------------------------------------
  | Club Dashboard Routes
  |--------------------------------------------------------------------------
  | Routes for club administrators to manage their events
 */

Route::middleware(['auth', 'club'])->prefix('club')->name('club.')->group(function () {
// Route::prefix('club')->name('club.')->group(function () {
    // Club Events Management
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [ClubEventsController::class, 'index'])->name('index');
        Route::get('/fetch', [ClubEventsController::class, 'fetch'])->name('fetch');
    });
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

/*
  |--------------------------------------------------------------------------
  | Development/Testing Routes
  |--------------------------------------------------------------------------
  | Remove or protect these in production
 */
//if (app()->environment('local')) {
//    // Test pages
//    Route::get('/test-403', function () {
//        abort(403);
//    });
//
//    Route::get('/test-404', function () {
//        abort(404);
//    });
//}
