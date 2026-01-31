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
        Schema::create('hotels', function (Blueprint $table) {
           $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The owner
        $table->string('name');
        $table->text('description');
        
        // Location
        $table->string('address');
        $table->string('city');
        $table->string('country');
        $table->string('zip_code');
        
        // Stats
        $table->integer('total_rooms')->nullable(); // Nullable for venues
        $table->integer('max_capacity');
        $table->string('price_range'); // $, $$, $$$, $$$$
        
        $table->string('status')->default('pending'); // Pending admin approval
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
