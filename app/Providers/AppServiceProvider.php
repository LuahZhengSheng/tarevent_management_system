<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     */
    public function register(): void {
        // Register services here if needed
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register enhanced model observers with notification support
        Event::observe(EventObserver::class);
        EventRegistration::observe(EventRegistrationObserver::class);

        // Morph map for polymorphic relationships
        Relation::morphMap([
            'club' => \App\Models\Club::class,
        ]);

        // Share unread notification count with all views (for navbar badge)
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('unreadNotificationsCount', auth()->user()->unread_notifications_count);
            }
        });
    }
}
