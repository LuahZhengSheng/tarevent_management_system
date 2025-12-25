<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) 添加 refund_requested_at
        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'refund_requested_at')) {
                $table->timestamp('refund_requested_at')
                      ->nullable()
                      ->after('refund_status');
            }
        });

        // 2) 把 refund_processed_at -> refund_completed_at
        if (Schema::hasColumn('event_registrations', 'refund_processed_at')
            && !Schema::hasColumn('event_registrations', 'refund_completed_at')) {

            Schema::table('event_registrations', function (Blueprint $table) {
                $table->renameColumn('refund_processed_at', 'refund_completed_at');
            });
        }
    }

    public function down(): void
    {
        // 回滚：把 refund_completed_at 改回 refund_processed_at
        if (Schema::hasColumn('event_registrations', 'refund_completed_at')
            && !Schema::hasColumn('event_registrations', 'refund_processed_at')) {

            Schema::table('event_registrations', function (Blueprint $table) {
                $table->renameColumn('refund_completed_at', 'refund_processed_at');
            });
        }

        // 删除 refund_requested_at
        Schema::table('event_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('event_registrations', 'refund_requested_at')) {
                $table->dropColumn('refund_requested_at');
            }
        });
    }
};
