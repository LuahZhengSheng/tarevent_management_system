<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'refund_auto_reject_at')) {
                $table->timestamp('refund_auto_reject_at')
                    ->nullable()
                    ->after('refund_completed_at') // 或者 after('refund_status') 按你实际顺序
                    ->comment('Auto-reject refund if not processed by this time');

                $table->index('refund_auto_reject_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('event_registrations', 'refund_auto_reject_at')) {
                try {
                    $table->dropIndex(['refund_auto_reject_at']);
                } catch (\Throwable $e) {
                    // 索引名不一致时忽略
                }

                $table->dropColumn('refund_auto_reject_at');
            }
        });
    }
};
