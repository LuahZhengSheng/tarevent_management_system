<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // 先删外键，再删列
            $table->dropForeign(['club_id']);
            $table->dropColumn('club_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('club_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }
};

