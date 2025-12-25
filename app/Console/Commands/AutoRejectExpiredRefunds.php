<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RefundService;
use Illuminate\Support\Facades\Log;

class AutoRejectExpiredRefunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refunds:auto-reject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically reject refund requests that have expired (no action taken within allowed time)';

    protected $refundService;

    /**
     * Create a new command instance.
     */
    public function __construct(RefundService $refundService)
    {
        parent::__construct();
        $this->refundService = $refundService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-rejection of expired refund requests...');
        
        try {
            $rejectedCount = $this->refundService->autoRejectExpiredRefunds();
            
            if ($rejectedCount > 0) {
                $this->info("✓ Successfully auto-rejected {$rejectedCount} expired refund request(s).");
                
                Log::info('Auto-rejection command completed', [
                    'rejected_count' => $rejectedCount,
                    'timestamp' => now(),
                ]);
            } else {
                $this->info('No expired refund requests found.');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during auto-rejection: ' . $e->getMessage());
            
            Log::error('Auto-rejection command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }
}