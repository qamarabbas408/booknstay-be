<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Free WiFi', 'slug' => 'wifi', 'icon' => 'Wifi'],
            ['name' => 'Pool', 'slug' => 'pool', 'icon' => 'Waves'],
            ['name' => 'Breakfast', 'slug' => 'breakfast', 'icon' => 'Coffee'],
            ['name' => 'Parking', 'slug' => 'parking', 'icon' => 'Car'],
            ['name' => 'Spa', 'slug' => 'spa', 'icon' => 'Sparkles'],
            ['name' => 'Gym', 'slug' => 'gym', 'icon' => 'Dumbbell'],
        ];

        foreach ($amenities as $item) {
            Amenity::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}