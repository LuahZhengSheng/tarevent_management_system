<?php
// database/migrations/2024_01_01_000004_create_post_tag_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            
            // 谁添加的这个标签
            $table->foreignId('tagged_by')->nullable()->constrained('users')->onDelete('set null');
            
            // 标签来源：user_manual（用户手动）, system_suggested（系统建议）
            $table->enum('source', ['user_manual', 'system_suggested'])->default('user_manual');
            
            // 标签显示顺序
            $table->integer('order')->default(0);
            
            // 是否确认（针对系统建议的标签）
            $table->boolean('is_confirmed')->default(true);
            
            $table->timestamps();
            
            // 确保同一 post 和 tag 组合唯一
            $table->unique(['post_id', 'tag_id']);
            
            $table->index('post_id');
            $table->index('tag_id');
            $table->index('tagged_by');
            $table->index(['post_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};
