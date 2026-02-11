<?php
namespace Database\Seeders;

use App\Models\User;
use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('admin123');
        $interests = Interest::all();

        // --- 2. EXISTING GUESTS EXAMPLE ---
        $guests = [
            ['name' => 'Alice Traveler', 'email' => 'alice@gmail.com'],
            ['name' => 'Bob Explorer', 'email' => 'bob@gmail.com'],
        ];

        foreach ($guests as $guestData) {
            $guest = User::create([
                'name' => $guestData['name'],
                'email' => $guestData['email'],
                'password' => $password,
                'role' => 'guest',
                'status' => 'active',
                'phone' => '+123456789',
            ]);

            $guest->interests()->attach(
                $interests->random(3)->pluck('id')->toArray()
            );
        }

        // --- 3. EXISTING VENDORS ---
        $vendors = [
            [
                'name' => 'Marco Polo',
                'email' => 'marco@grandhotel.com',
                'business' => 'Grand Ocean Resort',
                'type' => 'resort',
                'city' => 'Miami'
            ],
            [
                'name' => 'Sarah Venue',
                'email' => 'sarah@eventspace.com',
                'business' => 'The Urban Loft',
                'type' => 'event_venue',
                'city' => 'New York'
            ],
        ];

        foreach ($vendors as $v) {
            $vendorUser = User::create([
                'name' => $v['name'],
                'email' => $v['email'],
                'password' => $password,
                'role' => 'vendor',
                'status' => 'active',
            ]);

            $vendorUser->vendorProfile()->create([
                'business_name' => $v['business'],
                'business_type' => $v['type'],
                'business_email' => $v['email'],
                'website' => 'https://' . strtolower(str_replace(' ', '-', $v['business'])) . '.com',
            ]);

//            $vendorUser->hotels()->create([
//                'name' => $v['business'],
//                'description' => 'A wonderful ' . $v['type'] . ' located in the heart of ' . $v['city'] . '. Experience world-class service and luxury.',
//                'address' => '123 Main St',
//                'city' => $v['city'],
//                'country' => 'USA',
//                'zip_code' => '12345',
//                'total_rooms' => $v['type'] === 'event_venue' ? null : 45,
//                'max_capacity' => 200,
//                'price_range' => '$$$',
//                'status' => 'active',
//            ]);
        }

        // --- 5. ADDITIONAL VENDORS (20 more) ---
//for ($i = 1; $i <= 20; $i++) {
//    $vendorUser = User::create([
//        'name' => "Vendor User {$i}",
//        'email' => "vendor{$i}@example.com",
//        'password' => $password,
//        'role' => 'vendor',
//        'status' => 'active',
//    ]);
//
//    // Vendor profile
//    $businessName = "Business {$i}";
//    $vendorUser->vendorProfile()->create([
//        'business_name'  => $businessName,
//        'business_type'  => $i % 2 === 0 ? 'resort' : 'event_venue',
//        'business_email' => "vendor{$i}@example.com",
//        'website'        => 'https://' . strtolower(str_replace(' ', '-', $businessName)) . '.com',
//    ]);
//
//    // Hotel or venue record
//    $vendorUser->hotels()->create([
//        'name'         => $businessName,
//        'description'  => 'A wonderful ' . ($i % 2 === 0 ? 'resort' : 'event venue') . ' offering premium services.',
//        'address'      => '123 Vendor St',
//        'city'         => $i % 2 === 0 ? 'Miami' : 'New York',
//        'country'      => 'USA',
//        'zip_code'     => '1000' . $i,
//        'total_rooms'  => $i % 2 === 0 ? rand(20, 100) : null,
//        'max_capacity' => rand(100, 500),
//        'price_range'  => '$$',
//        'status'       => 'active',
//    ]);
//}


        // --- 4. ADDITIONAL USERS (20 more) ---
//        for ($i = 1; $i <= 20; $i++) {
//            $guest = User::create([
//                'name' => "Guest User {$i}",
//                'email' => "guest{$i}@example.com",
//                'password' => $password,
//                'role' => 'guest',
//                'status' => 'active',
//                'phone' => '+123456789',
//            ]);
//
//            $guest->interests()->attach(
//                $interests->random(3)->pluck('id')->toArray()
//            );
//        }
    }
}
