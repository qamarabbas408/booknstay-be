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
        Schema::create('amenities', function (Blueprint $table) {
           $table->id();
        $table->string('name'); // e.g., "Free WiFi"
        $table->string('slug')->unique(); // e.g., "wifi"
        $table->string('icon')->nullable(); // Lucide icon name: "Wifi", "Waves", etc.
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
