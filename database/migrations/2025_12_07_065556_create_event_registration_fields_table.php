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
        Schema::create('event_registration_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            
            // Field Definition
            $table->string('name', 100)->index(); // e.g., 'tshirt_size', 'dietary_requirements'
            $table->string('label'); // Display label for users
            $table->enum('type', [
                'text', 
                'textarea', 
                'select', 
                'radio', 
                'checkbox', 
                'date', 
                'number', 
                'email', 
                'tel'
            ])->default('text');
            
            // Field Configuration
            $table->boolean('required')->default(false);
            $table->json('options')->nullable(); // For select/radio/checkbox: ['Option 1', 'Option 2']
            $table->integer('order')->default(0)->index(); // Display order
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            
            // Validation
            $table->string('validation_rules')->nullable(); // Laravel validation rules
            $table->integer('min_length')->nullable();
            $table->integer('max_length')->nullable();
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            
            // Default Value
            $table->text('default_value')->nullable();
            
            // Conditional Logic (optional)
            $table->string('depends_on_field')->nullable(); // Field name this depends on
            $table->string('depends_on_value')->nullable(); // Value to show this field
            
            $table->timestamps();
            
            // Composite indexes
            $table->index(['event_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registration_fields');
    }
};