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
        Schema::create('event_subscriptions', function (Blueprint $table) {
            $table->id(); // 1. id (bigint unsigned auto_increment)

            // 2. user_id
            $table->foreignId('user_id')
                ->constrained('users') // 假设关联 users 表
                ->onDelete('cascade');

            // 3. event_id
            $table->foreignId('event_id')
                ->constrained('events') // 假设关联 events 表
                ->onDelete('cascade');

            // 4. is_active (tinyint(1), default 1)
            $table->boolean('is_active')->default(true);

            // 5. subscribed_at (timestamp, default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)
            // 注意：useCurrentOnUpdate() 对应 ON UPDATE CURRENT_TIMESTAMP
            $table->timestamp('subscribed_at')->useCurrent()->useCurrentOnUpdate();

            // 6. unsubscribed_at (timestamp, nullable)
            $table->timestamp('unsubscribed_at')->nullable();

            // 7. reason (varchar(255), nullable)
            $table->string('reason')->nullable();

            // 8 & 9. created_at, updated_at (timestamps)
            $table->timestamps();

            // 可选：为了防止同一个人重复订阅同一个活动，建议加个唯一索引
            // $table->unique(['user_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
}
};
