<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()
                    ->after('role')
                    ->constrained('clubs')
                    ->nullOnDelete();

            $table->string('profile_photo')->nullable()->after('club_id');
            $table->string('phone')->nullable()->after('profile_photo');
            $table->string('program')->nullable()->after('phone');
            $table->string('student_id')->nullable()->after('program');
            $table->json('interested_categories')->nullable()->after('student_id');
            $table->string('status')->default('active')->after('interested_categories');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
