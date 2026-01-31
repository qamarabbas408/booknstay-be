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
    Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Vendor
        $table->foreignId('event_category_id')->constrained();
        
        $table->string('title');
        $table->text('description');
        $table->string('location'); // City, Country
        $table->string('venue'); // Specific building
        
        $table->dateTime('start_time');
        $table->decimal('base_price', 10, 2);
        
        $table->boolean('is_featured')->default(false);
        $table->boolean('is_trending')->default(false);
        $table->string('status')->default('active');
        $table->string('image_path')->nullable();
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
