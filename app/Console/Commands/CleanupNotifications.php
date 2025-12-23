<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Services\NotificationService;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=30 : Days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notifications and unsubscribe users from past events';

    protected $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Cleaning up notifications older than {$days} days...");

        // Delete old read notifications
        $deletedCount = Notification::where('created_at', '<', now()->subDays($days))
            ->whereNotNull('read_at')
            ->delete();

        $this->info("Deleted {$deletedCount} old read notifications.");

        // Unsubscribe users from past events
        $this->info("Unsubscribing users from past events...");
        $unsubscribedCount = $this->notificationService->unsubscribeFromPastEvents();
        $this->info("Unsubscribed {$unsubscribedCount} users from past events.");

        $this->info('Cleanup completed successfully!');

        return Command::SUCCESS;
    }
}