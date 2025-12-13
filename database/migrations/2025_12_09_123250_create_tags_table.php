<?php
// database/migrations/2025_12_09_123250_create_tags_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->enum('type', ['official', 'community'])->default('community');
            $table->enum('status', ['active', 'pending', 'merged', 'banned'])->default('pending');
            $table->unsignedInteger('usage_count')->default(0);
            
            // 使用 unsignedBigInteger 而不是 foreignId（避免循环依赖）
            $table->unsignedBigInteger('merged_into_tag_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // 索引
            $table->index('slug');
            $table->index('name');
            $table->index(['status', 'usage_count']);
            $table->index('created_by');
            $table->index('merged_into_tag_id');
        });
        
        // 分开添加外键（避免创建时的循环依赖）
        Schema::table('tags', function (Blueprint $table) {
            // 自引用外键
            $table->foreign('merged_into_tag_id')
                  ->references('id')
                  ->on('tags')
                  ->onDelete('set null');
            
            // 用户外键
            if (Schema::hasTable('users')) {
                $table->foreign('created_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
                
                $table->foreign('approved_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
