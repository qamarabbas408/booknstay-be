<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->get();
        $categories = EventCategory::all();

        if ($vendors->isEmpty() || $categories->isEmpty()) {
            $this->command->error('Run UserSeeder and EventCategorySeeder first!');
            return;
        }

        $eventsData = [
            [
                'title' => 'Summer Music Festival 2026',
                'venue' => 'Wembley Arena',
                'location' => 'London, UK',
                'category' => 'Music',
                'tickets' => [
                    ['name' => 'General Admission', 'price' => 85, 'quantity' => 500],
                    ['name' => 'VIP Section', 'price' => 250, 'quantity' => 50],
                ]
            ],
            [
                'title' => 'Tech Innovation Expo',
                'venue' => 'Silicon Valley Center',
                'location' => 'San Francisco, USA',
                'category' => 'Technology',
                'tickets' => [
                    ['name' => 'Standard Pass', 'price' => 120, 'quantity' => 1000],
                    ['name' => 'Executive Pass', 'price' => 500, 'quantity' => 100],
                ]
            ],
            [
                'title' => 'Deep Sea Yoga Retreat',
                'venue' => 'Ocean View Pavilion',
                'location' => 'Bali, Indonesia',
                'category' => 'Wellness', // Ensure this exists or fallback to 'Art & Culture'
                'tickets' => [
                    ['name' => 'Early Bird', 'price' => 45, 'quantity' => 20],
                ]
            ]
        ];

        foreach ($eventsData as $data) {
            $category = $categories->where('name', $data['category'])->first() ?? $categories->random();
            $startTime = Carbon::now()->addMonths(rand(1, 5))->setHour(18);

            // 1. Create the Event
            $event = Event::create([
                'user_id' => $vendors->random()->id,
                'event_category_id' => $category->id,
                'title' => $data['title'],
                'description' => 'A spectacular ' . $data['category'] . ' event held at ' . $data['venue'],
                'location' => $data['location'],
                'venue' => $data['venue'],
                'start_time' => $startTime,
                'end_time' => $startTime->copy()->addHours(6),
                'base_price' => collect($data['tickets'])->min('price'), // Smallest price
                'total_capacity' => collect($data['tickets'])->sum('quantity'), // Total capacity
                'status' => 'active',
                'visibility' => 'public',
            ]);

            // 2. Create the Ticket Tiers for this event
            foreach ($data['tickets'] as $ticket) {
                $event->tickets()->create($ticket);
            }
        }
    }
}