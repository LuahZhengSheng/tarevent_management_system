<?php

// 2025_12_xx_xxxxxx_create_club_posts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('club_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->onDelete('cascade');
            $table->foreignId('club_id')
                ->constrained('clubs')
                ->onDelete('cascade');

            $table->boolean('pinned')->default(false);
            $table->string('status')->default('active'); // active / hidden / removed 之类
            $table->timestamps(); // created_at / updated_at

            $table->unique(['post_id', 'club_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_posts');
    }
};
