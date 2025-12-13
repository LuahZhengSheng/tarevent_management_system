<?php
// database/migrations/2025_12_09_123050_create_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->default('#6c757d');
            
            // 先创建字段，不加外键
            $table->unsignedBigInteger('parent_id')->nullable();
            
            $table->unsignedInteger('post_count')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // 索引
            $table->index('slug');
            $table->index('parent_id');
            $table->index(['is_active', 'order']);
        });
        
        // 分开添加自引用外键（避免创建时的问题）
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
