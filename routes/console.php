<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\EnhancedNotificationService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Clean up old notifications daily at 2 AM
Schedule::command('notifications:cleanup --days=30')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// 2. Unsubscribe from past events daily at 3 AM
Schedule::call(function () {
    app(EnhancedNotificationService::class)->unsubscribeFromPastEvents();
})->dailyAt('03:00');

// 3. 每分钟检查过期订单
Schedule::command('registrations:expire')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground(); // 注意：Windows 下 runInBackground 可能无效，但不影响功能

// 4. 自动拒绝超时未处理的退款请求（每天 00:00 跑一次）
Schedule::command('refunds:auto-reject')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// 5. Send event reminders (Commented out)
// Schedule::call(function () {
//     app(EnhancedNotificationService::class)->sendEventReminders();
// })->dailyAt('09:00');