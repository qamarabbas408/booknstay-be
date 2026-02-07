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
       Schema::table('hotels', function (Blueprint $table) {
        // We make these nullable so the V2 store action doesn't crash
        $table->integer('max_capacity')->nullable()->change();
        $table->integer('total_rooms')->nullable()->change();
        $table->decimal('base_price', 10, 2)->nullable()->change();
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
