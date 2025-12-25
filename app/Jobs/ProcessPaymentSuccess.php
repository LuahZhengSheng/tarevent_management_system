<?php

namespace App\Jobs;

use App\Models\EventRegistration;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; 
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPaymentSuccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $registration;

    /**
     * Create a new job instance.
     */
    public function __construct(EventRegistration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            // 这里调用原本耗时的服务方法
            $notificationService->sendPaymentSuccess($this->registration);
            
            Log::info('Queue: Payment success notification sent', ['id' => $this->registration->id]);
        } catch (\Exception $e) {
            Log::error('Queue: Failed to send payment notification', [
                'id' => $this->registration->id,
                'error' => $e->getMessage()
            ]);
            
            // 可以选择抛出异常让队列重试，或者直接吞掉
            // throw $e; 
        }
    }
}
