<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Refund fields
            if (!Schema::hasColumn('payments', 'refund_status')) {
                $table->enum('refund_status', ['pending', 'processing', 'completed', 'rejected', 'failed'])
                    ->nullable()
                    ->after('status')
                    ->comment('Refund request status');

                $table->index('refund_status');
            }

            if (!Schema::hasColumn('payments', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)
                    ->nullable()
                    ->after('refund_status')
                    ->comment('Amount to be refunded');
            }

            if (!Schema::hasColumn('payments', 'refund_transaction_id')) {
                $table->string('refund_transaction_id')
                    ->nullable()
                    ->after('refund_amount')
                    ->comment('Gateway refund transaction ID');
            }

            if (!Schema::hasColumn('payments', 'refund_requested_at')) {
                $table->timestamp('refund_requested_at')
                    ->nullable()
                    ->after('refund_transaction_id')
                    ->comment('When refund was requested');

                $table->index('refund_requested_at');
            }

            if (!Schema::hasColumn('payments', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')
                    ->nullable()
                    ->after('refund_requested_at')
                    ->comment('When refund was completed/rejected');

                $table->index('refund_processed_at');
            }

            if (!Schema::hasColumn('payments', 'refund_reason')) {
                $table->text('refund_reason')
                    ->nullable()
                    ->after('refund_processed_at')
                    ->comment('User provided reason for refund');
            }

            if (!Schema::hasColumn('payments', 'refund_requested_by')) {
                $table->unsignedBigInteger('refund_requested_by')
                    ->nullable()
                    ->after('refund_reason')
                    ->comment('User ID who requested refund');

                $table->foreign('refund_requested_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('payments', 'refund_processed_by')) {
                $table->unsignedBigInteger('refund_processed_by')
                    ->nullable()
                    ->after('refund_requested_by')
                    ->comment('Admin/Organizer who processed refund');

                $table->foreign('refund_processed_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('payments', 'refund_rejection_reason')) {
                $table->text('refund_rejection_reason')
                    ->nullable()
                    ->after('refund_processed_by')
                    ->comment('Admin reason for rejecting refund');
            }

            if (!Schema::hasColumn('payments', 'refund_idempotency_key')) {
                $table->string('refund_idempotency_key')
                    ->nullable()
                    ->after('refund_rejection_reason')
                    ->comment('Idempotency key for gateway refund');

                // 建议用 index 而不是 unique（避免重复 key 迁移报错）
                $table->index('refund_idempotency_key');
            }

            if (!Schema::hasColumn('payments', 'refund_metadata')) {
                $table->json('refund_metadata')
                    ->nullable()
                    ->after('refund_idempotency_key')
                    ->comment('Additional refund metadata from gateway');
            }
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('event_registrations', 'refund_auto_reject_at')) {
                $table->timestamp('refund_auto_reject_at')
                    ->nullable()
                    ->after('refund_completed_at')
                    ->comment('Auto-reject refund if not processed by this time');

                $table->index('refund_auto_reject_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // 先尝试 drop FK（如果存在）
            if (Schema::hasColumn('payments', 'refund_requested_by')) {
                try {
                    $table->dropForeign(['refund_requested_by']);
                } catch (\Throwable $e) {
                    // 忽略：某些环境下外键名不同
                }
            }
            if (Schema::hasColumn('payments', 'refund_processed_by')) {
                try {
                    $table->dropForeign(['refund_processed_by']);
                } catch (\Throwable $e) {
                    // 忽略
                }
            }

            // drop indexes（用 try-catch 避免索引名不一致报错）
            foreach (['refund_status', 'refund_requested_at', 'refund_processed_at', 'refund_idempotency_key'] as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    try {
                        $table->dropIndex([$col]);
                    } catch (\Throwable $e) {
                        // 忽略
                    }
                }
            }

            // drop columns
            $cols = [
                'refund_status',
                'refund_amount',
                'refund_transaction_id',
                'refund_requested_at',
                'refund_processed_at',
                'refund_reason',
                'refund_requested_by',
                'refund_processed_by',
                'refund_rejection_reason',
                'refund_idempotency_key',
                'refund_metadata',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('payments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('event_registrations', 'refund_auto_reject_at')) {
                try {
                    $table->dropIndex(['refund_auto_reject_at']);
                } catch (\Throwable $e) {
                    // 忽略
                }
                $table->dropColumn('refund_auto_reject_at');
            }
        });
    }
};
