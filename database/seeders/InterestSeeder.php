<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            ['name' => 'Music Festivals', 'icon' => 'Music'],
            ['name' => 'Luxury Stays', 'icon' => 'Sparkles'],
            ['name' => 'Beach & Sun', 'icon' => 'Palmtree'],
            ['name' => 'Adventure & Trekking', 'icon' => 'Compass'],
            ['name' => 'Cultural Tours', 'icon' => 'Map'],
            ['name' => 'Nightlife', 'icon' => 'Heart'],
            ['name' => 'Wellness & Spa', 'icon' => 'Wind'],
            ['name' => 'Business Events', 'icon' => 'Briefcase'],
        ];

        foreach ($interests as $interest) {
            Interest::updateOrCreate(
                ['slug' => Str::slug($interest['name'])], // Unique identifier
                [
                    'name' => $interest['name'],
                    'icon' => $interest['icon'],
                ]
            );
        }
    }
}