<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // category_id 通常有外键，先 drop 外键再改 nullable 再加回去
            // 外键名默认是 posts_category_id_foreign
            $table->dropForeign(['category_id']);
        });

        Schema::table('posts', function (Blueprint $table) {
            // 允许草稿不选分类
            $table->unsignedBigInteger('category_id')->nullable()->change();

            // 这些你截图已经是 nullable，但写在这里也安全（确保环境一致）
            $table->longText('content')->nullable()->change();
            $table->longText('media_paths')->nullable()->change();
            $table->timestamp('published_at')->nullable()->change();
        });

        Schema::table('posts', function (Blueprint $table) {
            // 加回外键（允许 null 时，建议 nullOnDelete）
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('posts', function (Blueprint $table) {
            // 回滚：category_id 变回不允许 null（注意：如果已有 null 数据会回滚失败）
            $table->unsignedBigInteger('category_id')->nullable(false)->change();

            // 回滚其它字段（按你原本想要的结构决定；通常不建议把 content 改回 NOT NULL）
            $table->longText('content')->nullable(false)->change();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->cascadeOnDelete();
        });
    }
};
