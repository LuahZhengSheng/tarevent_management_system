<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up old notifications daily at 2 AM
        $schedule->command('notifications:cleanup --days=30')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();

        // Unsubscribe from past events daily at 3 AM
        $schedule->call(function () {
            app(\App\Services\EnhancedNotificationService::class)
                ->unsubscribeFromPastEvents();
        })->dailyAt('03:00');
        
        // Send event reminders daily at 9 AM
        // (You can implement this in your NotificationService)
        // $schedule->call(function () {
        //     app(\App\Services\EnhancedNotificationService::class)
        //         ->sendEventReminders();
        // })->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}