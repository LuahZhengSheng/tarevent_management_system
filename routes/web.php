<?php

use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Event\EventRegistrationController;
//use App\Http\Controllers\Forum\ForumController;
//use App\Http\Controllers\Club\ClubController;
//use App\Http\Controllers\User\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

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
//
//// Public Event Browsing (No Auth Required)
//Route::get('/events', [EventController::class, 'index'])->name('events.index');
//Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
//
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
//Route::middleware('auth')->group(function () {
//    
//    // User Profile Management
//    Route::prefix('profile')->name('profile.')->group(function () {
//        Route::get('/', [UserController::class, 'show'])->name('show');
//        Route::get('/edit', [UserController::class, 'edit'])->name('edit');
//        Route::put('/update', [UserController::class, 'update'])->name('update');
//        Route::get('/change-password', [UserController::class, 'showChangePasswordForm'])->name('change-password');
//        Route::put('/change-password', [UserController::class, 'changePassword'])->name('update-password');
//    });
//
//    // Event Registration (Students)
//    Route::prefix('events')->name('events.')->group(function () {
//        Route::post('/{event}/register', [EventRegistrationController::class, 'store'])
//            ->name('register');
//        Route::delete('/{event}/unregister', [EventRegistrationController::class, 'destroy'])
//            ->name('unregister');
//    });
//
//    // Payment Routes
//    Route::prefix('registrations')->name('registrations.')->group(function () {
//        Route::get('/{registration}/payment', [EventRegistrationController::class, 'payment'])
//            ->name('payment');
//        Route::post('/{registration}/pay', [EventRegistrationController::class, 'pay'])
//            ->name('pay');
//    });
//
//    // My Events (User's registered events)
//    Route::get('/my-events', [EventRegistrationController::class, 'myEvents'])
//        ->name('events.my');
//
//    // Forum Interactions (Authenticated Users)
//    Route::prefix('forum')->name('forum.')->group(function () {
//        Route::post('/posts', [ForumController::class, 'store'])->name('posts.store');
//        Route::put('/posts/{post}', [ForumController::class, 'update'])->name('posts.update');
//        Route::delete('/posts/{post}', [ForumController::class, 'destroy'])->name('posts.destroy');
//        
//        Route::post('/posts/{post}/comments', [ForumController::class, 'storeComment'])->name('comments.store');
//        Route::delete('/comments/{comment}', [ForumController::class, 'destroyComment'])->name('comments.destroy');
//    });
//});

/*
|--------------------------------------------------------------------------
| Club Admin Routes
|--------------------------------------------------------------------------
| Only accessible by users with 'club' role
*/
//Route::middleware(['auth', 'club'])->prefix('events')->name('events.')->group(function () {
Route::prefix('events')->name('events.')->group(function () {
    // Event Management (Create, Edit, Delete)
    Route::get('/create', [EventController::class, 'create'])->name('create');
    Route::post('/', [EventController::class, 'store'])->name('store');
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
//    
//    // Dashboard
//    Route::get('/dashboard', function () {
//        return view('admin.dashboard');
//    })->name('dashboard');
//
//    // User Management
//    Route::prefix('users')->name('users.')->group(function () {
//        Route::get('/', [UserController::class, 'index'])->name('index');
//        Route::get('/{user}', [UserController::class, 'adminShow'])->name('show');
//        Route::put('/{user}/suspend', [UserController::class, 'suspend'])->name('suspend');
//        Route::put('/{user}/activate', [UserController::class, 'activate'])->name('activate');
//        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
//    });
//
//    // Admin Management
//    Route::prefix('admins')->name('admins.')->group(function () {
//        Route::get('/', [UserController::class, 'adminsIndex'])->name('index');
//        Route::get('/create', [UserController::class, 'createAdmin'])->name('create');
//        Route::post('/', [UserController::class, 'storeAdmin'])->name('store');
//        Route::delete('/{user}', [UserController::class, 'destroyAdmin'])->name('destroy');
//    });
//
//    // All Events Management
//    Route::prefix('events')->name('events.')->group(function () {
//        Route::get('/', [EventController::class, 'adminIndex'])->name('index');
//        Route::post('/{event}/approve', [EventController::class, 'approve'])->name('approve');
//        Route::post('/{event}/reject', [EventController::class, 'reject'])->name('reject');
//    });
//
//    // Club Management
//    Route::prefix('clubs')->name('clubs.')->group(function () {
//        Route::get('/', [ClubController::class, 'adminIndex'])->name('index');
//        Route::get('/create', [ClubController::class, 'create'])->name('create');
//        Route::post('/', [ClubController::class, 'store'])->name('store');
//        Route::get('/{club}/edit', [ClubController::class, 'edit'])->name('edit');
//        Route::put('/{club}', [ClubController::class, 'update'])->name('update');
//        Route::delete('/{club}', [ClubController::class, 'destroy'])->name('destroy');
//    });
//
//    // Reports & Analytics
//    Route::prefix('reports')->name('reports.')->group(function () {
//        Route::get('/events', [EventController::class, 'eventsReport'])->name('events');
//        Route::get('/registrations', [EventRegistrationController::class, 'registrationsReport'])->name('registrations');
//        Route::get('/payments', [EventRegistrationController::class, 'paymentsReport'])->name('payments');
//    });
//});

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