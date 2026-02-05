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
        Schema::table('events', function (Blueprint $table) {
         $table->json('highlights')->nullable()->after('description'); // Array of strings

        });

        Schema::table('event_tickets', function (Blueprint $table) {
        $table->text('description')->nullable()->after('name');
        $table->json('features')->nullable()->after('description'); // Array of strings
        $table->boolean('is_popular')->default(false)->after('features');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
