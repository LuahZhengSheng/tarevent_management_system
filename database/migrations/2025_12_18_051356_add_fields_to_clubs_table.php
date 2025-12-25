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
            $table->string('slug')->unique()->after('name');
            $table->string('email')->nullable()->after('slug');
            $table->string('phone')->nullable()->after('email');
            $table->string('logo')->nullable()->after('phone');
            $table->unsignedBigInteger('created_by')->after('logo');
            $table->timestamp('approved_at')->nullable()->after('created_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');

            // Add foreign key constraints
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
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
