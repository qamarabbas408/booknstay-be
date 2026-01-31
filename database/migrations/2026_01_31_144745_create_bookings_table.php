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
        Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Guest
        
        // Polymorphic columns: bookable_id and bookable_type
        // This links to either a Hotel model or an Event model
        $table->morphs('bookable'); 
        
        $table->string('booking_code')->unique(); // e.g., BNS-H-12345
        
        // Timing
        $table->dateTime('check_in')->nullable();  // Used for Hotels
        $table->dateTime('check_out')->nullable(); // Used for Hotels
        $table->dateTime('event_date')->nullable(); // Used for Events
        
        // Counts
        $table->integer('guests_count')->default(1);
        $table->integer('rooms_count')->nullable();
        $table->integer('tickets_count')->nullable();
        
        // Pricing & Status
        $table->decimal('total_price', 10, 2);
        $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
        
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
