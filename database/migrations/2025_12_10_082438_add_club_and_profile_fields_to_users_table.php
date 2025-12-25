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
            if (!Schema::hasColumn('users', 'club_id')) {
                $table->foreignId('club_id')->nullable()
                        ->after('role')
                        ->constrained('clubs')
                        ->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'profile_photo')) {
                $table->string('profile_photo')->nullable()->after('club_id');
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('profile_photo');
            }

            if (!Schema::hasColumn('users', 'program')) {
                $table->string('program')->nullable()->after('phone');
            }

            if (!Schema::hasColumn('users', 'student_id')) {
                $table->string('student_id')->nullable()->after('program');
            }

            if (!Schema::hasColumn('users', 'interested_categories')) {
                $table->json('interested_categories')->nullable()->after('student_id');
            }


            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('interested_categories');
            }

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
