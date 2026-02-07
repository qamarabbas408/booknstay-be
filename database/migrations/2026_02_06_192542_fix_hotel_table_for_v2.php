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
        //
          Schema::table('hotels', function (Blueprint $table) {
        // Make ALL legacy V1 columns nullable so V2 doesn't crash
        $table->string('price_range')->nullable()->change(); // The one causing the current error
        $table->integer('max_capacity')->nullable()->change();
        $table->integer('total_rooms')->nullable()->change();
        $table->decimal('base_price', 10, 2)->nullable()->change();
        $table->string('address')->nullable()->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
