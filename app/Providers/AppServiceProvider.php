<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Services\PostService;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Observers\EventObserver;
use App\Observers\EventRegistrationObserver;
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
        Relation::morphMap([
            'club' => \App\Models\Club::class,
        ]);
        // Other bootstrap logic can go here
    }
}
