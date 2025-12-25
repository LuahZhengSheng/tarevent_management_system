<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        // 修改 refund_status 列，重新定义它的 ENUM 范围
        DB::statement("ALTER TABLE event_registrations MODIFY COLUMN refund_status ENUM('pending', 'rejected', 'completed') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        // 回滚时改回原来的定义
        DB::statement("ALTER TABLE event_registrations MODIFY COLUMN refund_status ENUM('pending', 'processed', 'rejected') NULL");
    }
};
