<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            if (Schema::hasColumn('post_comments', 'content') && !Schema::hasColumn('post_comments', 'body')) {
                $table->renameColumn('content', 'body');
            }

            $table->foreignId('reply_to_user_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('users')
                ->nullOnDelete();

            // media_paths：结构跟 Post 的 media_paths 类似（array cast），支持 image/video
            $table->json('media_paths')->nullable()->after('body');

            // indexes
            $table->index(['post_id', 'reply_to_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            if (Schema::hasColumn('post_comments', 'reply_to_user_id')) {
                $table->dropConstrainedForeignId('reply_to_user_id');
            }
            if (Schema::hasColumn('post_comments', 'media_paths')) {
                $table->dropColumn('media_paths');
            }
            if (Schema::hasColumn('post_comments', 'body') && !Schema::hasColumn('post_comments', 'content')) {
                $table->renameColumn('body', 'content');
            }
        });
    }
};
