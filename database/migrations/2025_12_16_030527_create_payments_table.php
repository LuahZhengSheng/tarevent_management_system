<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add payment gateway specific fields
            
            // For Stripe Payment Intent ID (separate from transaction_id)
            $table->string('payment_intent_id')->nullable()->after('transaction_id');
            
            // Payer information
            $table->string('payer_email')->nullable()->after('payment_intent_id');
            $table->string('payer_name')->nullable()->after('payer_email');
            
            // Additional metadata (JSON field for flexibility)
            // Can store: card last4, card brand, paypal payer_id, etc.
            $table->json('metadata')->nullable()->after('payer_name');
            
            // Refund tracking
            $table->string('refund_status')->nullable()->after('metadata')
                  ->comment('null / pending / processing / completed / rejected');
            $table->decimal('refund_amount', 8, 2)->nullable()->after('refund_status');
            $table->string('refund_transaction_id')->nullable()->after('refund_amount');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_transaction_id');
            $table->timestamp('refund_processed_at')->nullable()->after('refund_requested_at');
            $table->text('refund_reason')->nullable()->after('refund_processed_at');
            
            // Error tracking
            $table->text('error_message')->nullable()->after('refund_reason');
            $table->string('error_code')->nullable()->after('error_message');
            
            // Add indexes for better query performance
            $table->index('transaction_id');
            $table->index(['event_registration_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['method', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['event_registration_id', 'status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['method', 'status']);
            $table->dropIndex(['created_at']);
            
            // Drop columns
            $table->dropColumn([
                'payment_intent_id',
                'payer_email',
                'payer_name',
                'metadata',
                'refund_status',
                'refund_amount',
                'refund_transaction_id',
                'refund_requested_at',
                'refund_processed_at',
                'refund_reason',
                'error_message',
                'error_code',
            ]);
        });
    }
};