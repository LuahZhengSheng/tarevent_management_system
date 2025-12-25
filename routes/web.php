<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Event\EventRegistrationController;
use App\Http\Controllers\Club\ClubEventsController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\ProfileController;
//use App\Http\Controllers\Forum\ForumController;
//use App\Http\Controllers\Club\ClubController;
//use App\Http\Controllers\User\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Club\ClubController;

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

// Test routes for club creation form
Route::get('/test/clubs/create', function () {
    return view('clubs.test_create');
})->name('test.clubs.create');

// Test route for admin club creation form
Route::get('/test/admin/clubs/create', function () {
    return view('clubs.test_admin_create');
})->name('test.admin.clubs.create');

// Test route for adding member to club
Route::get('/test/clubs/add-member', function () {
    return view('clubs.test_add_member');
})->name('test.clubs.add-member');

// Test route for adding member action
Route::middleware(['auth', 'club'])->post('/test/clubs/{club}/members/{user}/add', function (\App\Models\Club $club, \App\Models\User $user, \App\Services\ClubFacade $facade) {
    $facade->addMember($club, $user);
    return redirect()->back()->with('success', 'Member added successfully.');
})->name('test.clubs.members.add');

// Removed: Test routes for club approval (admin-created clubs are now immediately active)

// Test route for club deactivation form
Route::get('/test/clubs/{club}/deactivate', function ($clubId) {
    return view('clubs.test_deactivate', ['clubId' => $clubId]);
})->name('test.clubs.deactivate');

// Test route for club deactivation action
Route::put('/test/clubs/{club}/deactivate', function ($clubId, \App\Services\ClubFacade $facade, \Illuminate\Http\Request $request) {
    $club = \App\Models\Club::findOrFail($clubId);
    $deactivatedBy = auth()->user() ?? \App\Models\User::first();
    $reason = $request->input('reason');

    $facade->deactivateClub($club, $deactivatedBy, $reason);

    return redirect()->back()->with('success', 'Club deactivated successfully.');
})->name('test.clubs.deactivate.store');

// TEST ROUTE – REMOVE BEFORE SUBMISSION
Route::middleware(['auth'])->get('/test/clubs/all', function () {
    return view('clubs.test_all');
})->name('clubs.test_all');

// TEST ROUTE – REMOVE BEFORE SUBMISSION
Route::get('/test/club-api', function () {
    return view('test.club_api_test');
})->name('test.club.api');

// TEST ROUTE – REMOVE BEFORE SUBMISSION (Simple Version)
Route::middleware(['auth'])->get('/test/join-club-modal-simple', function () {
    return view('test.join_club_modal_simple');
})->name('test.join.club.modal.simple');

// TEST ROUTE – REMOVE BEFORE SUBMISSION
Route::middleware(['auth'])->get('/test/user-clubs-api', function () {
    return view('test.user_clubs_api_test');
})->name('test.user.clubs.api');

// TEST ROUTE – REMOVE BEFORE SUBMISSION
Route::get('/test/club-user-api', function () {
    return view('test.club_user_api_test');
})->name('test.club.user.api');

// TEST ROUTE – REMOVE BEFORE SUBMISSION
Route::middleware(['auth'])->get('/test/select-club-modal', function () {
    return view('test.select_club_modal_test');
})->name('test.select.club.modal');

use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\MyPostController;
use App\Http\Controllers\Forum\CommentController;
use App\Http\Controllers\Forum\LikeController;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
 */

// Load authentication routes (login, register, password reset, etc.)
require __DIR__.'/auth.php';

// Public Routes


Route::redirect('/', '/events')->name('home');

// Route::get('/login', function () {
//     // 简单占位，可以改成你的登录页面
//     return view('welcome'); // 确保有 resources/views/auth/login.blade.php
// })->name('login');

// // Authentication Routes
// Route::middleware('guest')->group(function () {
//     Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
//     Route::post('/login', [LoginController::class, 'login']);
//     Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register');
//     Route::post('/register', [UserController::class, 'register']);
// });

// Route::post('/logout', [LoginController::class, 'logout'])
//     ->middleware('auth')
//     ->name('logout');

// Public Event Browsing (No Auth Required)
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/fetch', [EventController::class, 'fetchPublic'])->name('events.fetch');
//Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
//// Public Club Browsing
//Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
//Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');
//// Public Forum Browsing
//Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
//Route::get('/forum/{post}', [ForumController::class, 'show'])->name('forum.show');

/*
  |--------------------------------------------------------------------------
  | Webhook Routes
  |--------------------------------------------------------------------------
 */

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])
        ->name('webhook.stripe');

Route::post('/webhook/paypal', [PayPalWebhookController::class, 'handle'])
        ->name('webhook.paypal');

/*
  |--------------------------------------------------------------------------
  | Authenticated Routes
  |--------------------------------------------------------------------------
 */

// Notification Routes
Route::middleware(['auth'])->group(function () {

    // Notification management
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // List notifications with filters
        Route::get('/', [NotificationController::class, 'index'])->name('index');

        // Show notification detail
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');

        // Mark as read
        Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])
                ->name('mark-as-read');

        // Mark as unread
        Route::post('/{notification}/mark-as-unread', [NotificationController::class, 'markAsUnread'])
                ->name('mark-as-unread');

        // Mark all as read
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
                ->name('mark-all-read');

        // Delete notification
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])
                ->name('destroy');

        // Batch delete
        Route::post('/batch-delete', [NotificationController::class, 'batchDelete'])
                ->name('batch-delete');

        // Delete all read
        Route::post('/delete-all-read', [NotificationController::class, 'deleteAllRead'])
                ->name('delete-all-read');
    });

    // API endpoint for unread count (for navbar badge)
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('api.notifications.unread-count');
});

/*
  |--------------------------------------------------------------------------
  | User Routes
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

    // My Events (User's registered events)
    Route::get('/my-events', [EventRegistrationController::class, 'myEvents'])
            ->name('events.my');

    // Fetch my events via AJAX
    Route::get('/my-events/fetch', [EventRegistrationController::class, 'fetchMyEvents'])
            ->name('events.my.fetch');

    // Cancel Registration
    Route::delete('/registrations/{registration}', [EventRegistrationController::class, 'destroy'])
            ->name('registrations.cancel');
    
    // Registration History
    Route::view('/registration-history', 'events.registration-history');

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
    Route::patch('/{event}/publish', [EventController::class, 'publish'])->name('publish');
    Route::post('/{event}/cancel', [EventController::class, 'cancel'])->name('cancel');

    // AJAX Field Validation
    Route::post('/validate-field', [EventController::class, 'validateField'])
            ->name('validate-field');

    // Event Registrations Management
    Route::get('/{event}/registrations', [EventRegistrationController::class, 'index'])
            ->name('registrations.index');
    Route::get('/{event}/registrations/export', [EventRegistrationController::class, 'export'])
            ->name('registrations.export');

    // Refund management (organizer/admin)
    Route::get('/refunds/manage', [RefundController::class, 'manage'])
            ->name('refunds.manage');

    Route::get('/refunds/fetch', [RefundController::class, 'fetchRequests'])
            ->name('refunds.fetch');

    Route::post('/refunds/{payment}/approve', [RefundController::class, 'approve'])
            ->name('refunds.approve');

    Route::post('/refunds/{payment}/reject', [RefundController::class, 'reject'])
            ->name('refunds.reject');
});

Route::middleware(['auth', 'club'])->prefix('club')->name('club.')->group(function () {
// Route::prefix('club')->name('club.')->group(function () {
    // Club Events Management
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/', [ClubEventsController::class, 'index'])->name('index');
        Route::get('/fetch', [ClubEventsController::class, 'fetch'])->name('fetch');
    });
});

// Club Management Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/clubs/create', function () {
        return view('admin.clubs.create');
    })->name('admin.clubs.create');
    
    Route::post('/clubs', [ClubController::class, 'store'])
        ->name('clubs.store');
});

Route::middleware(['auth', 'club'])->group(function () {
    Route::put(
        '/clubs/{club}/members/{user}',
        [ClubController::class, 'updateMemberRole']
    )->name('clubs.members.updateRole');
});

/*
  |--------------------------------------------------------------------------
  | Admin Routes
  |--------------------------------------------------------------------------
  | Only accessible by users with 'admin' role
 */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
    });

    // Administrator Management
    Route::prefix('administrators')->name('administrators.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/create', [AdminController::class, 'create'])->name('create');
        Route::post('/', [AdminController::class, 'store'])->name('store');
        Route::patch('/{admin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{admin}', [AdminController::class, 'show'])->name('show');
        Route::get('/{admin}/edit', [AdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [AdminController::class, 'update'])->name('update');
    });

    // Permission Management (Super Admin Only)
    Route::middleware('super_admin')->prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/{admin}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [PermissionController::class, 'update'])->name('update');
    });

    // Admin Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/update', [ProfileController::class, 'update'])->name('update');
    });
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
  | Payment Routes
  |--------------------------------------------------------------------------
 */

Route::middleware(['auth', 'user', 'check.active.user'])->group(function () {
    // Payment Page (Checkout Landing Page)
    Route::get('/registrations/{registration}/payment', [PaymentController::class, 'payment'])
            ->name('registrations.payment');

    // ----------------------------------------------------
    // Stripe Payment Routes
    // ----------------------------------------------------
    // 创建 Stripe Session (用于跳转到 Stripe 托管页面)
//    Route::post('/payments/stripe/create-session', [PaymentController::class, 'createStripeSession'])
//        ->name('payments.stripe.create-session');
//
//    // Stripe 成功回调页面 (从 Stripe 跳转回来)
//    Route::get('/payments/stripe/success', [PaymentController::class, 'stripeSuccess'])
//        ->name('payments.stripe.success');
    // Stripe PaymentIntent 路由
    Route::post('/payments/create-intent', [PaymentController::class, 'createIntent'])
            ->name('payments.create-intent');

    // 确认支付
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment'])
            ->name('payments.confirm');

    // ----------------------------------------------------
    // PayPal Payment Routes
    // ----------------------------------------------------
    Route::post('/payments/paypal/create-order', [PaymentController::class, 'createPayPalOrder'])
            ->name('payments.paypal.create-order');

    Route::post('/payments/paypal/capture-order', [PaymentController::class, 'capturePayPalOrder'])
            ->name('payments.paypal.capture-order');

    // Payment receipt and refund routes
    Route::get('/registrations/{registration}/receipt', [PaymentController::class, 'receipt'])
            ->name('registrations.receipt');

    Route::get('/registrations/{registration}/check-status', [PaymentController::class, 'checkStatus'])
            ->name('registrations.check-status');

    Route::get('/payments/{payment}/download-receipt', [RefundController::class, 'downloadReceipt'])
            ->name('payments.download-receipt');

    Route::get('/payments/{payment}/download-refund-receipt', [RefundController::class, 'downloadRefundReceipt'])
            ->name('payments.download-refund-receipt');

    // Refund request (user)
    Route::post('/registrations/{registration}/request-refund', [RefundController::class, 'request'])
            ->name('registrations.request-refund');
});

Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

/*
  |--------------------------------------------------------------------------
  | Development/Testing Routes
  |--------------------------------------------------------------------------
  | Remove or protect these in production
 */
if (app()->environment('local')) {
    // Test pages
    Route::get('/test-403', function () {
        abort(403);
    });

    Route::get('/test-404', function () {
        abort(404);
    });
}
