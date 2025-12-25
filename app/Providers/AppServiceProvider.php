<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\PostService;
use App\Contracts\MailServiceInterface;
use App\Services\MailService;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
use App\Observers\PaymentObserver;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register PostService as singleton
        $this->app->singleton(PostService::class, function ($app) {
            return new PostService();
        });

        // Bind MailServiceInterface to MailService implementation
        $this->app->singleton(MailServiceInterface::class, MailService::class);
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        // Use Bootstrap pagination
        Paginator::useBootstrapFive();
        
        // Register model observers
        Event::observe(EventObserver::class);
        EventRegistration::observe(EventRegistrationObserver::class);
        Payment::observe(PaymentObserver::class);

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
