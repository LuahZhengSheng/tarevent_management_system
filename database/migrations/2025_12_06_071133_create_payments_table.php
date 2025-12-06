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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // 关联到活动和报名记录、用户
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_registration_id')
                  ->constrained('event_registrations')
                  ->cascadeOnDelete();

            // 支付相关字段
            $table->decimal('amount', 8, 2);          // 支付金额
            $table->string('method');                 // paypal / stripe
            $table->string('transaction_id')->unique(); // 第三方支付 id（PayPal/Stripe）
            $table->string('status')->default('pending'); // pending / success / failed
            $table->timestamp('paid_at')->nullable(); // 支付完成时间

            $table->timestamps();                     // created_at / updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
