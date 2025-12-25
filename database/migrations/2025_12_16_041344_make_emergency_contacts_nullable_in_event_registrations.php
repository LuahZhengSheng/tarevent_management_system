<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('event_registrations', function (Blueprint $table) {
            // change() 方法需要 doctrine/dbal 包，如果没有安装可能会报错
            // 如果报错，先运行 composer require doctrine/dbal
            $table->string('emergency_contact_name')->nullable()->change();
            $table->string('emergency_contact_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('emergency_contact_name')->nullable(false)->change();
            $table->string('emergency_contact_phone')->nullable(false)->change();
        });
    }
};
