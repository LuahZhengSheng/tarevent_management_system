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
    public function boot(): void {
        // Register model observers
        Event::observe(EventObserver::class);
        EventRegistration::observe(EventRegistrationObserver::class);
        Relation::morphMap([
            'club' => \App\Models\Club::class,
        ]);
        // Other bootstrap logic can go here
    }
}
