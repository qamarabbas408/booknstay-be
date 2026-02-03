<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            InterestSeeder::class,
            UserSeeder::class,
            AmenitySeeder::class,
            HotelSeeder::class,
            EventCategorySeeder::class,
            EventSeeder::class,
            BookingSeeder::class,       // 6. Bookings (needs hotels and events)

        ]);

    }
}
