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
        Schema::table('event_registrations', function (Blueprint $table) {
            // 付款过期时间
            $table->timestamp('expires_at')->nullable()->after('status');
            
            // 支付网关信息
            $table->string('payment_gateway', 20)->nullable()->after('expires_at')
                  ->comment('stripe, paypal');
            
            // 网关 Session/Order ID
            $table->string('gateway_session_id')->nullable()->after('payment_gateway')
                  ->comment('Stripe Session ID or PayPal Order ID');
            
            // 过期通知标记（避免重复发送）
            $table->boolean('expiry_notified')->default(false)->after('gateway_session_id');
            
            // 添加索引以提高查询性能
            $table->index(['status', 'expires_at'], 'idx_status_expires');
            $table->index('gateway_session_id', 'idx_gateway_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex('idx_status_expires');
            $table->dropIndex('idx_gateway_session');
            
            $table->dropColumn([
                'expires_at',
                'payment_gateway',
                'gateway_session_id',
                'expiry_notified'
            ]);
        });
    }
};