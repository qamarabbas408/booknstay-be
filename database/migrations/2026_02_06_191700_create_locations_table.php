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
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        
        // Polymorphic Link (locatable_id and locatable_type)
        // This connects the location to a Hotel or an Event
        $table->morphs('locatable'); 

        $table->string('country');
        $table->string('city');
        $table->text('full_address');
        $table->string('zip_code')->nullable();

        // GPS Coordinates
        // Latitude: -90 to +90. Decimal(10, 8) provides ~1mm precision.
        // Longitude: -180 to +180. Decimal(11, 8) provides same.
        $table->decimal('latitude', 10, 8)->nullable();
        $table->decimal('longitude', 11, 8)->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
