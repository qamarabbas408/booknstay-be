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
            // Tax rate as a percentage (e.g., 12.50)
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            // Fixed service fee per night or per stay
            $table->decimal('service_fee', 10, 2)->default(0.00);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            //
        });
    }
};
