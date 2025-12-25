<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireRegistrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire pending registrations that exceeded payment time limit';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕒 Checking for expired registrations...');

        try {
            DB::beginTransaction();

            // 查找所有已过期的 pending 订单
            $expiredRegistrations = EventRegistration::where('status', 'pending_payment')
                ->where('expires_at', '<', now())
                ->where('expiry_notified', false) // 避免重复处理
                ->lockForUpdate()
                ->get();

            if ($expiredRegistrations->isEmpty()) {
                $this->info('No expired registrations found.');
                DB::commit();
                return 0;
            }

            $count = $expiredRegistrations->count();
            $this->info("⏰ Found {$count} expired registration(s). Processing...");

            foreach ($expiredRegistrations as $registration) {
                // 更新状态为 cancelled
                $registration->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Payment time expired (30 minutes)',
//                    'expiry_notified' => true, // 标记为已处理
                ]);

                $this->line("  → Expired: Registration #{$registration->id} for Event #{$registration->event_id}");

                Log::info('Registration expired automatically', [
                    'registration_id' => $registration->id,
                    'user_id' => $registration->user_id,
                    'event_id' => $registration->event_id,
                    'expires_at' => $registration->expires_at,
                ]);
            }

            DB::commit();

            $this->info("Successfully expired {$count} registration(s).");
            
            // Observer 会自动触发邮件和站内通知
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error('Failed to expire registrations: ' . $e->getMessage());
            Log::error('Expire registrations command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }
}