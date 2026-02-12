<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();
        $categories = EventCategory::all();

        if ($vendors->isEmpty() || $categories->isEmpty()) {
            $this->command->error("Run UserSeeder and EventCategorySeeder first!");
            return;
        }

        $eventsData = [
            [
                'title' => 'Summer Music Festival 2026',
                'category' => 'Music',
                'description' => 'Experience the biggest summer music festival with international headliners, multiple stages, and unforgettable performances.',
                'venue' => 'Wembley Arena',
                'highlights' => ['3 Main stages', '20+ Global Artists', 'VIP Lounges', 'Gourmet Food Court'],
                'location' => ['city' => 'London', 'country' => 'UK', 'address' => 'Wembley, London HA9 0WS', 'lat' => 51.5582, 'lng' => -0.2797],
                'tickets' => [
                    ['name' => 'General Admission', 'price' => 85, 'quantity' => 2000, 'features' => ['General entry', 'Standing area']],
                    ['name' => 'VIP Experience', 'price' => 180, 'quantity' => 200, 'features' => ['Fast track entry', 'VIP lounge access', 'Complimentary drinks']],
                ],
                'featured' => true,
                'trending' => true
            ],
            [
                'title' => 'Tech Innovation Expo',
                'category' => 'Technology',
                'description' => 'The premier event for tech enthusiasts. Discover the latest in AI, Robotics, and future software trends.',
                'venue' => 'Moscone Center',
                'highlights' => ['Live AI Demos', 'Startup Pitch Night', 'Keynotes from Big Tech', 'VR Experience Zone'],
                'location' => ['city' => 'San Francisco', 'country' => 'USA', 'address' => '747 Howard St, San Francisco, CA 94103', 'lat' => 37.7842, 'lng' => -122.4019],
                'tickets' => [
                    ['name' => 'Standard Pass', 'price' => 120, 'quantity' => 500, 'features' => ['Access to all halls', 'Digital certificate']],
                    ['name' => 'Premium Pass', 'price' => 350, 'quantity' => 50, 'features' => ['All halls access', 'Lunch with speakers', 'Workshops']],
                ],
                'featured' => true,
                'trending' => false
            ],
            [
                'title' => 'Gourmet Wine & Dine',
                'category' => 'Food & Wine',
                'description' => 'An elegant evening of fine wines and gourmet food pairings curated by Michelin-star chefs.',
                'venue' => 'The Glass House',
                'highlights' => ['5-Course Meal', 'Sommelier Guidance', 'Live Jazz Quartet', 'Gift Bag'],
                'location' => ['city' => 'Paris', 'country' => 'France', 'address' => '10 Avenue d\'Iéna, 75116 Paris', 'lat' => 48.8647, 'lng' => 2.2939],
                'tickets' => [
                    ['name' => 'Standard Seat', 'price' => 150, 'quantity' => 80, 'features' => ['Dinner included', '3 Wine pairings']],
                    ['name' => 'VIP Wine Tasting', 'price' => 250, 'quantity' => 20, 'features' => ['Premium seating', '6 Wine pairings', 'Meet the chef']],
                ],
                'featured' => false,
                'trending' => true
            ]
        ];

        foreach ($eventsData as $data) {
            DB::transaction(function () use ($data, $vendors, $categories) {
                $category = $categories->where('name', $data['category'])->first() ?? $categories->random();
                $startTime = Carbon::now()->addMonths(rand(1, 4))->setHour(18)->setMinute(0);

                // 1. Create Event
                $event = Event::create([
                    'user_id' => $vendors->random()->id,
                    'event_category_id' => $category->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'venue' => $data['venue'],
                    'highlights' => $data['highlights'],
                    'start_time' => $startTime,
                    'end_time' => $startTime->copy()->addHours(6),
                    'base_price' => collect($data['tickets'])->min('price'),
                    'total_capacity' => collect($data['tickets'])->sum('quantity'),
                    'is_featured' => $data['featured'],
                    'is_trending' => $data['trending'],
                    'status' => 'active',
                    'visibility' => 'public',
                ]);

                // 2. Create Location (The location relationship)
                $event->location()->create([
                    'city' => $data['location']['city'],
                    'country' => $data['location']['country'],
                    'full_address' => $data['location']['address'],
                    'latitude' => $data['location']['lat'],
                    'longitude' => $data['location']['lng'],
                ]);

                // 3. Create Ticket Tiers
                foreach ($data['tickets'] as $ticket) {
                    $event->tickets()->create([
                        'name' => $ticket['name'],
                        'price' => $ticket['price'],
                        'quantity' => $ticket['quantity'],
                        'features' => $ticket['features'],
                    ]);
                }
            });
        }
    }
}
