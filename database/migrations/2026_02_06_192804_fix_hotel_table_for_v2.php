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
      
        $table->string('country')->nullable()->change();
        $table->string('city')->nullable()->change();
        $table->string('zip_code')->nullable()->change();

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
