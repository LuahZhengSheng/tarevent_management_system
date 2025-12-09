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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
//            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            
            // Registration Details
            $table->string('registration_number', 50)->unique()->index();
            $table->enum('status', ['pending_payment', 'confirmed', 'cancelled', 'waitlisted'])
                  ->default('pending_payment')
                  ->index();
            
            // Personal Information
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 20);
            $table->string('student_id', 50)->index();
            $table->string('program');
            
            // Special Requirements
//            $table->text('dietary_requirements')->nullable();
//            $table->text('special_requirements')->nullable();
            
            // Emergency Contact
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone', 20);
            
            // Custom Registration Data (JSON)
            $table->json('registration_data')->nullable();
            
            // Attendance Tracking
            $table->boolean('attended')->default(false)->index();
            $table->timestamp('checked_in_at')->nullable();
            
            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Refund Management
            $table->enum('refund_status', ['pending', 'processed', 'rejected'])->nullable();
            $table->timestamp('refund_processed_at')->nullable();
            
            // Admin Notes
            $table->text('notes')->nullable();
            
            // Security & Audit Trail
//            $table->string('ip_address', 45)->nullable(); // IPv4 and IPv6 support
//            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Composite Indexes for Common Queries
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'attended']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};