<?php
// database/migrations/2025_12_09_123150_create_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onDelete('set null');
            
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->longText('content');
            
            $table->enum('visibility', ['public', 'club_only'])->default('public');
            $table->enum('status', ['draft', 'published'])->default('published');
            
            $table->json('media_paths')->nullable();
            
            // Counters
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('slug');
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index('created_at');
            $table->index('views_count');
            $table->index('likes_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
