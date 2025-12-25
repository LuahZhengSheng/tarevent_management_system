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
        Schema::table('clubs', function (Blueprint $table) {
            
            // 1. Slug
            if (!Schema::hasColumn('clubs', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }

            // 2. Email
            if (!Schema::hasColumn('clubs', 'email')) {
                $table->string('email')->nullable()->after('slug');
            }

            // 3. Phone
            if (!Schema::hasColumn('clubs', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            // 4. Logo
            if (!Schema::hasColumn('clubs', 'logo')) {
                $table->string('logo')->nullable()->after('phone');
            }

            // 5. Created By (包含 Foreign Key)
            if (!Schema::hasColumn('clubs', 'created_by')) {
                $table->unsignedBigInteger('created_by')->after('logo');
                
                // 只有在创建了列之后才添加外键约束
                $table->foreign('created_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('restrict');
            }

            // 6. Approved At
            if (!Schema::hasColumn('clubs', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('created_by');
            }

            // 7. Approved By (包含 Foreign Key)
            if (!Schema::hasColumn('clubs', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');

                // 只有在创建了列之后才添加外键约束
                $table->foreign('approved_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);

            // Drop columns
            $table->dropColumn([
                'slug',
                'email',
                'phone',
                'logo',
                'created_by',
                'approved_at',
                'approved_by',
            ]);
        });
    }
};
