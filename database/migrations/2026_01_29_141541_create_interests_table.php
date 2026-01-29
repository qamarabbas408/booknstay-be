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
        Schema::create('interests', function (Blueprint $table) {
                   $table->id();
        $table->string('name')->unique(); // e.g., "Music Festivals"
        $table->string('slug')->unique(); // e.g., "music-festivals"
        $table->string('icon')->nullable(); // Lucide icon name or emoji
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};
