<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forum_notifications', function (Blueprint $table) {
            $table->id();

            // receiver
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // actor (who triggered)
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            // context
            $table->foreignId('post_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('post_comments')->cascadeOnDelete();

            // type: post_like / post_save / post_comment / comment_reply_mention
            $table->string('type', 50);

            // UI payload
            $table->string('title')->nullable();        // optional
            $table->string('message', 255);             // one line text
            $table->string('url', 500);                 // link to post/comment

            // read state
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at']);
            $table->index(['post_id', 'comment_id']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_notifications');
    }
};
