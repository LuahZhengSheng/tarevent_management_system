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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // event_created, event_updated, event_cancelled, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like event_id, changes, etc.
            $table->string('channel')->default('database'); // database, email, both
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });

        // Event subscriptions table - tracks which users are subscribed to which events
        Schema::create('event_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('reason')->nullable(); // event_ended, user_cancelled, event_cancelled
            $table->timestamps();
            
            // Unique constraint - one subscription per user per event
            $table->unique(['user_id', 'event_id']);
            $table->index(['event_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_subscriptions');
        Schema::dropIfExists('notifications');
    }
};