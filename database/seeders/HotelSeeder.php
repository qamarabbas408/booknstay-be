<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Amenity;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get our existing vendors
        $grandHotelVendor = User::where('email', 'marco@grandhotel.com')->first();
        $urbanLoftVendor = User::where('email', 'sarah@eventspace.com')->first();

        if (! $grandHotelVendor || ! $urbanLoftVendor) {
            $this->command->error('Vendors not found. Please run UserSeeder first.');

            return;
        }

        // 2. Define initial hotels (your existing 4)
        $hotels = [
            [
                'user_id' => $grandHotelVendor->id,
                'name' => 'The Blue Horizon Resort',
                'description' => 'A stunning oceanfront resort featuring private beaches, infinity pools, and world-class dining. Perfect for summer festivals and relaxing stays.',
                'address' => '888 Coastal Way',
                'city' => 'Miami',
                'country' => 'USA',
                'zip_code' => '33101',
                'total_rooms' => 120,
                'max_capacity' => 450,
                'price_range' => '$$$$',
                'status' => 'active',
            ],
            [
                'user_id' => $urbanLoftVendor->id,
                'name' => 'Industrial Chic Suites',
                'description' => 'A boutique hotel in a converted warehouse. Features exposed brick, high ceilings, and an underground jazz club. Ideal for city explorers.',
                'address' => '42 Iron Street',
                'city' => 'New York',
                'country' => 'USA',
                'zip_code' => '10001',
                'total_rooms' => 35,
                'max_capacity' => 80,
                'price_range' => '$$',
                'status' => 'active',
            ],
            [
                'user_id' => $grandHotelVendor->id,
                'name' => 'Neon Palms Hotel',
                'description' => 'A retro-themed hotel with a modern twist. Located in the heart of the nightlife district, hosting weekly pool parties.',
                'address' => '500 Sunset Blvd',
                'city' => 'Miami',
                'country' => 'USA',
                'zip_code' => '33139',
                'total_rooms' => 85,
                'max_capacity' => 250,
                'price_range' => '$$$',
                'status' => 'active',
            ],
            [
                'user_id' => $urbanLoftVendor->id,
                'name' => 'The Skyline Pavilion',
                'description' => 'This venue doubles as a luxury hotel and a premier event space. The rooftop garden offers 360-degree views of the Manhattan skyline.',
                'address' => '100 Wall Street',
                'city' => 'New York',
                'country' => 'USA',
                'zip_code' => '10005',
                'total_rooms' => 15,
                'max_capacity' => 500,
                'price_range' => '$$$$',
                'status' => 'pending',
            ],
        ];

        // 3. Add 16 more hotels to reach 20
        for ($i = 1; $i <= 16; $i++) {
            $hotels[] = [
                'user_id' => $i % 2 === 0 ? $grandHotelVendor->id : $urbanLoftVendor->id,
                'name' => "Sample Hotel {$i}",
                'description' => "This is a sample description for hotel {$i}, offering great amenities and comfortable stays.",
                'address' => "{$i} Example Street",
                'city' => $i % 2 === 0 ? 'Miami' : 'New York',
                'country' => 'USA',
                'zip_code' => '1000'.$i,
                'total_rooms' => 50 + $i,
                'max_capacity' => 100 + ($i * 10),
                'price_range' => $i % 3 === 0 ? '$$$$' : '$$',
                'status' => 'active',
                'base_price' => $i * 50,

            ];
        }
$allAmenityIds = Amenity::pluck('id')->toArray();

        // 4. Create hotels and attach images
        foreach ($hotels as $hotelData) {
            $hotel = Hotel::create($hotelData);

            $hotel->images()->createMany([
                ['image_path' => 'hotels/sample1.jpg', 'is_primary' => true],
                ['image_path' => 'hotels/sample2.jpg', 'is_primary' => false],
            ]);
            $hotel->amenities()->attach(
                array_rand(array_flip($allAmenityIds), rand(3, 5))
            );
        }
    }
}
