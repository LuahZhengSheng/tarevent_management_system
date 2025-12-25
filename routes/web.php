<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Event\EventRegistrationController;
use App\Http\Controllers\Event\PaymentController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use App\Http\Controllers\Webhook\PayPalWebhookController;
use App\Http\Controllers\Event\RefundController;
use App\Http\Controllers\Club\ClubEventsController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Forum\ForumMessageController;
//use App\Http\Controllers\Forum\ForumController;
//use App\Http\Controllers\Club\ClubController;
//use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\MyPostController;
use App\Http\Controllers\Forum\CommentController;
use App\Http\Controllers\Forum\LikeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Club\ClubController;
use App\Http\Controllers\TestApiController;
use App\Models\Club;
use Illuminate\Http\Request;

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

// TEST ROUTE
//Route::middleware(['auth'])->get('/test/post-feed/{club}', function (\App\Models\Club $club) {
//    $user = auth()->user();
//
//    if (!$user || !$club->members()->where('users.id', $user->id)->exists()) {
//        // 选一种：403 或 redirect
//        abort(403); 
////        return redirect('/somewhere')->with('error', 'Forbidden');
//    }
//
//    return view('test.post_feed', compact('club'));
//})->name('test.post-feed');
//if (app()->environment('local')) {
//    Route::middleware('auth')->get('/test/post-feed', function () {
//
//        // 这里决定“当前 club”（示例：固定 club 1）
//        $club = \App\Models\Club::findOrFail(1);
//
//        return view('test.post_feed', compact('club'));
//    })->name('test.post-feed');
//}

if (app()->environment('local')) {
    Route::middleware('auth')->get('/test/club-forum', function (Request $request) {
        $clubs = Club::query()
                ->where('status', 'active')   // 如果你有 status 字段
                ->orderBy('name')
                ->get();

        return view('test.club_forum_picker', compact('clubs'));
    })->name('test.club-forum');
}

if (app()->environment('local')) {

    Route::middleware('auth')->post('/club/select', function (Request $request) {
        $clubId = (int) $request->input('club_id');
        abort_if(!$clubId, 422);

        $club = Club::findOrFail($clubId);

        // 必须验证：用户确实是这个 club member
        abort_unless($club->members()->where('users.id', auth()->id())->exists(), 403);

        $request->session()->put('active_club_id', $club->id);

        return redirect()->route('test.club_show');
    })->name('club.select');
}

if (app()->environment('local')) {
    Route::middleware('auth')->get('/club', function (Request $request) {
        $clubId = (int) $request->session()->get('active_club_id');
        abort_if(!$clubId, 404);

        $club = Club::findOrFail($clubId);

        // 保险：再校验一次成员
        abort_unless($club->members()->where('users.id', auth()->id())->exists(), 403);

        return view('test.club_show', compact('club'));
    })->name('test.club_show');
}


Route::get('/test/forum-user-stats', [TestApiController::class, 'testForumUserStats'])
        ->name('test.forum-user-stats');

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

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
 */

// Load authentication routes (login, register, password reset, etc.)
require __DIR__ . '/auth.php';

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
        
    // ==========================================
    // 1. Event Registration
    // ==========================================
    Route::prefix('events/{event}')->name('events.register.')->group(function () {
        Route::get('/register', [EventRegistrationController::class, 'create'])->name('create');
        Route::post('/register', [EventRegistrationController::class, 'store'])->name('store');
    });

    // AJAX Validation for Registration Form
    Route::post('/events/register/validate-field', [EventRegistrationController::class, 'validateField'])
            ->name('events.register.validate');

    // Cancel Registration
    Route::delete('/registrations/{registration}', [EventRegistrationController::class, 'destroy'])
            ->name('registrations.cancel');


    // ==========================================
    // 2. My Events & Dashboard
    // ==========================================
    // My Events Page
    Route::get('/my-events', [EventRegistrationController::class, 'myEvents'])
            ->name('events.my');

    // Fetch My Events (AJAX)
    Route::get('/my-events/fetch', [EventRegistrationController::class, 'fetchMyEvents'])
            ->name('events.my.fetch');

    
    // ==========================================
    // 3. Registration History
    // ==========================================
    // History Page
    Route::get('/events/{event}/registrations/history', [EventRegistrationController::class, 'history'])
        ->name('registrations.history');
        
    // Fetch History Data (AJAX)
    Route::get('/events/{event}/registrations/fetch-history', [EventRegistrationController::class, 'fetchHistory'])
        ->name('registrations.fetchHistory');


    // ==========================================
    // 4. Payment System
    // ==========================================
    
    // --- Checkout / Landing ---
    Route::get('/registrations/{registration}/payment', [PaymentController::class, 'payment'])
            ->name('registrations.payment');

    // --- Payment History ---
    Route::get('/payments/history', [PaymentController::class, 'history'])
        ->name('payments.history');
    Route::get('/payments/fetch-history', [PaymentController::class, 'fetchHistory'])
        ->name('payments.fetchHistory');

    // --- Stripe Integration ---
    Route::post('/payments/create-intent', [PaymentController::class, 'createIntent'])
            ->name('payments.create-intent');
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment'])
            ->name('payments.confirm');

    // --- PayPal Integration ---
    Route::post('/payments/paypal/create-order', [PaymentController::class, 'createPayPalOrder'])
            ->name('payments.paypal.create-order');
    Route::post('/payments/paypal/capture-order', [PaymentController::class, 'capturePayPalOrder'])
            ->name('payments.paypal.capture-order');

    // --- Payment Status Check ---
    Route::get('/registrations/{registration}/check-status', [PaymentController::class, 'checkStatus'])
            ->name('registrations.check-status');

            
    // ==========================================
    // 5. Receipts & Refunds
    // ==========================================
    
    // View Receipt (HTML Page)
    Route::get('/registrations/{registration}/receipt', [PaymentController::class, 'receipt'])
            ->name('registrations.receipt');
            
    // Download Payment Receipt (PDF) 
    Route::get('/payments/{payment}/download-receipt', [PaymentController::class, 'downloadReceipt'])
            ->name('payments.download-receipt');

    // Download Refund Receipt (PDF) -> Pointing to PaymentController
    Route::get('/payments/{payment}/download-refund-receipt', [RefundController::class, 'downloadRefundReceipt'])
            ->name('payments.download-refund-receipt');

    // Request Refund
    Route::post('/registrations/{registration}/request-refund', [RefundController::class, 'request'])
            ->name('registrations.request-refund');
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

        // Messages (forum_notifications)
        Route::get('messages', [ForumMessageController::class, 'index'])->name('messages.index');
        Route::post('messages/mark-all-read', [ForumMessageController::class, 'markAllAsRead'])->name('messages.mark-all-read');
        Route::post('messages/{notification}/read', [ForumMessageController::class, 'markAsRead'])->name('messages.read');
        Route::post('messages/{notification}/unread', [ForumMessageController::class, 'markAsUnread'])->name('messages.unread');
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
    
    // Approve/Reject join requests
    Route::post(
        '/clubs/{club}/join-requests/{user}/approve',
        [ClubController::class, 'approveJoin']
    )->name('clubs.join.approve');
    
    Route::post(
        '/clubs/{club}/join-requests/{user}/reject',
        [ClubController::class, 'rejectJoin']
    )->name('clubs.join.reject');
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
