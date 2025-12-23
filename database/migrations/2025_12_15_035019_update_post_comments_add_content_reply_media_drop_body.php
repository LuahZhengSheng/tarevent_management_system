<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table) {
            // 1) add new columns
            if (!Schema::hasColumn('post_comments', 'reply_to_user_id')) {
                $table->foreignId('reply_to_user_id')
                    ->nullable()
                    ->after('parent_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('post_comments', 'content')) {
                $table->text('content')->nullable()->after('reply_to_user_id');
            }

            if (!Schema::hasColumn('post_comments', 'media_paths')) {
                $table->json('media_paths')->nullable()->after('content');
            }
        });

        // 2) migrate old data body -> content
        if (Schema::hasColumn('post_comments', 'body')) {
            DB::statement("UPDATE post_comments SET content = body WHERE content IS NULL");
        }

        // 3) drop old column
        Schema::table('post_comments', function (Blueprint $table) {
            if (Schema::hasColumn('post_comments', 'body')) {
                $table->dropColumn('body');
            }
        });
    }

    public function down(): void
    {
        // rollback: re-add body, move content -> body, drop new columns
        Schema::table('post_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('post_comments', 'body')) {
                $table->text('body')->nullable();
            }
        });

        if (Schema::hasColumn('post_comments', 'content')) {
            DB::statement("UPDATE post_comments SET body = content WHERE body IS NULL");
        }

        Schema::table('post_comments', function (Blueprint $table) {
            if (Schema::hasColumn('post_comments', 'reply_to_user_id')) {
                $table->dropConstrainedForeignId('reply_to_user_id');
            }
            if (Schema::hasColumn('post_comments', 'media_paths')) {
                $table->dropColumn('media_paths');
            }
            if (Schema::hasColumn('post_comments', 'content')) {
                $table->dropColumn('content');
            }
        });
    }
};
