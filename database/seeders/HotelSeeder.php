<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\RoomTier; // If you still use the catalog, otherwise ignore
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get Vendors and Amenities
        $vendors = User::where('role', 'vendor')->get();
        $amenities = Amenity::all();

        if ($vendors->isEmpty() || $amenities->isEmpty()) {
            $this->command->error("Please run UserSeeder and AmenitySeeder first!");
            return;
        }

        $hotelsData = [
            [
                'name' => 'Grand Azure Resort & Spa',
                'description' => 'A paradise escape in the heart of the Maldives. Features private villas, crystal clear lagoons, and world-class underwater dining.',
                'stars' => 5,
                'tax' => 12.00,
                'fee' => 25.00,
                'location' => ['city' => 'Male', 'country' => 'Maldives', 'address' => 'Villingili Island', 'lat' => 4.1755, 'lng' => 73.5093],
                'rooms' => [
                    ['name' => 'Ocean Villa', 'price' => 450, 'cap' => 2, 'inv' => 10],
                    ['name' => 'Family Beach Suite', 'price' => 850, 'cap' => 4, 'inv' => 5],
                ]
            ],
            [
                'name' => 'The Urban Boutique Hotel',
                'description' => 'Sophisticated city living in Manhattan. Exposed brick walls, rooftop jazz bar, and artisanal coffee in every room.',
                'stars' => 4,
                'tax' => 8.50,
                'fee' => 15.00,
                'location' => ['city' => 'New York', 'country' => 'USA', 'address' => '242 West 30th St', 'lat' => 40.7128, 'lng' => -74.0060],
                'rooms' => [
                    ['name' => 'Standard Queen', 'price' => 180, 'cap' => 2, 'inv' => 25],
                    ['name' => 'Executive Loft', 'price' => 320, 'cap' => 2, 'inv' => 10],
                ]
            ],
            [
                'name' => 'Budget Stay Guesthouse',
                'description' => 'Clean, comfortable and affordable. Perfect for backpackers and solo travelers looking for a central location.',
                'stars' => 3,
                'tax' => 5.00,
                'fee' => 0.00,
                'location' => ['city' => 'Male', 'country' => 'Maldives', 'address' => 'Maafannu District', 'lat' => 4.1748, 'lng' => 73.5100],
                'rooms' => [
                    ['name' => 'Economy Single', 'price' => 45, 'cap' => 1, 'inv' => 15],
                    ['name' => 'Standard Double', 'price' => 75, 'cap' => 2, 'inv' => 10],
                ]
            ],
            [
                'name' => 'Summit Conference Center',
                'description' => 'Specifically designed for business travelers. High-speed fiber internet, 12 meeting rooms, and ergonomic workspaces.',
                'stars' => 4,
                'tax' => 10.00,
                'fee' => 10.00,
                'location' => ['city' => 'London', 'country' => 'UK', 'address' => 'Canary Wharf', 'lat' => 51.5055, 'lng' => -0.0235],
                'rooms' => [
                    ['name' => 'Business Studio', 'price' => 210, 'cap' => 1, 'inv' => 40],
                ]
            ]
        ];

        foreach ($hotelsData as $data) {
            DB::transaction(function () use ($data, $vendors, $amenities) {
                // A. Create Hotel
                $hotel = Hotel::create([
                    'user_id' => $vendors->random()->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'star_rating' => $data['stars'],
                    'tax_rate' => $data['tax'],
                    'service_fee' => $data['fee'],
                    'status' => 'active',
                ]);

                // B. Create Location (Polymorphic)
                $hotel->location()->create([
                    'city' => $data['location']['city'],
                    'country' => $data['location']['country'],
                    'full_address' => $data['location']['address'],
                    'latitude' => $data['location']['lat'],
                    'longitude' => $data['location']['lng'],
                ]);

                // C. Attach Amenities (Random 4)
                $hotel->amenities()->attach($amenities->random(4)->pluck('id'));

                // D. Create Room Types (The V2 "Products")
                foreach ($data['rooms'] as $room) {
                    $hotel->roomTypes()->create([
                        'name' => $room['name'],
                        'base_price' => $room['price'],
                        'max_occupancy' => $room['cap'],
                        'total_inventory' => $room['inv'],
                        'status' => 'active',
                    ]);
                }

                // E. Create 5 Placeholder Images for the Gallery UI
                for ($i = 1; $i <= 5; $i++) {
                    $hotel->images()->create([
                        'image_path' => "hotels/sample_{$i}.jpg",
                        'is_primary' => ($i === 1),
                    ]);
                }
            });
        }
    }
}
