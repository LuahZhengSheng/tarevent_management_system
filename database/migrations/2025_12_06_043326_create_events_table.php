<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('title');
            $table->text('description');
            $table->string('slug')->unique()->nullable();

            // Organizer (Polymorphic)
            $table->unsignedBigInteger('organizer_id');
            $table->string('organizer_type')->default('club'); // club / user
            // DateTime Fields
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('registration_start_time');
            $table->dateTime('registration_end_time');

            // Location
            $table->string('venue');
            $table->string('location_map_url')->nullable();

            // Classification
            $table->string('category')->nullable();
            $table->json('tags')->nullable();

            // Visibility & Status
            $table->boolean('is_public')->default(true);
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->text('cancelled_reason')->nullable();

            // Payment Configuration
            $table->boolean('is_paid')->default(false);
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->boolean('refund_available')->default(false);

            // Capacity
            $table->integer('max_participants')->nullable();

            // Media
            $table->string('poster_path')->nullable();

            // Contact Information
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            // Additional Configuration
            $table->json('requirements')->nullable();
            $table->json('registration_fields')->nullable();

            // Registration / Rules
            $table->boolean('allow_cancellation')->default(true);
            $table->boolean('require_emergency_contact')->default(false);
            $table->boolean('require_dietary_info')->default(false);
            $table->boolean('require_special_requirements')->default(false);
            $table->text('registration_instructions')->nullable();

            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('organizer_id');
            $table->index('organizer_type');
            $table->index('category');
            $table->index('status');
            $table->index('is_public');
            $table->index('start_time');
            $table->index('created_by');

            // Foreign Keys
            $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');

            $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('events');
    }
};
