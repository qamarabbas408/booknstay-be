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

        // --- 1. SUPER ADMIN ---
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@booknstay.com',
            'password' => $password,
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        // --- 2. GUESTS (Travelers) ---
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

            // Assign 3 random interests to each guest
            $guest->interests()->attach(
                $interests->random(3)->pluck('id')->toArray()
            );
        }

        // --- 3. VENDORS (Business Owners) ---
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
                'status' => 'active', // Set to active so they can bypass the waiting room
            ]);

            // Create Vendor Profile
            $vendorUser->vendorProfile()->create([
                'business_name' => $v['business'],
                'business_type' => $v['type'],
                'business_email' => $v['email'],
                'website' => 'https://' . strtolower(str_replace(' ', '-', $v['business'])) . '.com',
            ]);

            // Create First Hotel Listing
            $vendorUser->hotels()->create([
                'name' => $v['business'],
                'description' => 'A wonderful ' . $v['type'] . ' located in the heart of ' . $v['city'] . '. Experience world-class service and luxury.',
                'address' => '123 Main St',
                'city' => $v['city'],
                'country' => 'USA',
                'zip_code' => '12345',
                'total_rooms' => $v['type'] === 'event_venue' ? null : 45,
                'max_capacity' => 200,
                'price_range' => '$$$',
                'status' => 'active',
            ]);
        }
    }
}
