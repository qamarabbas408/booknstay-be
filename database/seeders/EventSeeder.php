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
    // --- Existing 3 events ---
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
        'category' => 'Wellness',
        'tickets' => [
            ['name' => 'Early Bird', 'price' => 45, 'quantity' => 20],
        ]
    ],

    // --- 20 more events ---
    [
        'title' => 'Global Business Summit',
        'venue' => 'Dubai Expo Center',
        'location' => 'Dubai, UAE',
        'category' => 'Business',
        'tickets' => [
            ['name' => 'Delegate Pass', 'price' => 300, 'quantity' => 200],
            ['name' => 'VIP Delegate', 'price' => 800, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'International Food Carnival',
        'venue' => 'Central Park',
        'location' => 'New York, USA',
        'category' => 'Food & Wine',
        'tickets' => [
            ['name' => 'Entry Ticket', 'price' => 20, 'quantity' => 1000],
            ['name' => 'Chef’s Table', 'price' => 150, 'quantity' => 100],
        ]
    ],
    [
        'title' => 'Marathon for Charity',
        'venue' => 'City Stadium',
        'location' => 'Boston, USA',
        'category' => 'Sports',
        'tickets' => [
            ['name' => 'Runner Registration', 'price' => 50, 'quantity' => 2000],
        ]
    ],
    [
        'title' => 'Art & Culture Expo',
        'venue' => 'Louvre Annex',
        'location' => 'Paris, France',
        'category' => 'Art & Culture',
        'tickets' => [
            ['name' => 'Gallery Pass', 'price' => 40, 'quantity' => 500],
            ['name' => 'VIP Tour', 'price' => 120, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'Jazz Night Under the Stars',
        'venue' => 'Millennium Park',
        'location' => 'Chicago, USA',
        'category' => 'Music',
        'tickets' => [
            ['name' => 'General Admission', 'price' => 35, 'quantity' => 400],
            ['name' => 'Front Row', 'price' => 90, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'Gaming Convention 2026',
        'venue' => 'Expo Center',
        'location' => 'Los Angeles, USA',
        'category' => 'Gaming',
        'tickets' => [
            ['name' => 'Standard Pass', 'price' => 60, 'quantity' => 2000],
            ['name' => 'VIP Gamer', 'price' => 200, 'quantity' => 200],
        ]
    ],
    [
        'title' => 'Fashion Week Showcase',
        'venue' => 'Fashion Hall',
        'location' => 'Milan, Italy',
        'category' => 'Fashion',
        'tickets' => [
            ['name' => 'Runway Pass', 'price' => 150, 'quantity' => 500],
            ['name' => 'Designer Meet & Greet', 'price' => 400, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'Science & Innovation Fair',
        'venue' => 'Tech Arena',
        'location' => 'Berlin, Germany',
        'category' => 'Science',
        'tickets' => [
            ['name' => 'Exhibit Pass', 'price' => 30, 'quantity' => 1000],
            ['name' => 'Workshop Access', 'price' => 100, 'quantity' => 200],
        ]
    ],
    [
        'title' => 'Literary Festival',
        'venue' => 'Royal Hall',
        'location' => 'London, UK',
        'category' => 'Literature',
        'tickets' => [
            ['name' => 'Entry Pass', 'price' => 25, 'quantity' => 600],
            ['name' => 'Author Meet', 'price' => 75, 'quantity' => 100],
        ]
    ],
    [
        'title' => 'Wellness Retreat',
        'venue' => 'Oceanfront Retreat Center',
        'location' => 'Bali, Indonesia',
        'category' => 'Health & Wellness',
        'tickets' => [
            ['name' => 'Retreat Pass', 'price' => 250, 'quantity' => 150],
        ]
    ],
    [
        'title' => 'Startup Pitch Night',
        'venue' => 'Innovation Hub',
        'location' => 'San Francisco, USA',
        'category' => 'Business',
        'tickets' => [
            ['name' => 'Audience Pass', 'price' => 25, 'quantity' => 200],
            ['name' => 'Investor Pass', 'price' => 100, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'International Film Festival',
        'venue' => 'City Theater',
        'location' => 'Toronto, Canada',
        'category' => 'Film & Theater',
        'tickets' => [
            ['name' => 'Screening Pass', 'price' => 80, 'quantity' => 500],
            ['name' => 'VIP Screening', 'price' => 200, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'Charity Gala Dinner',
        'venue' => 'Grand Ballroom',
        'location' => 'Los Angeles, USA',
        'category' => 'Charity & Causes',
        'tickets' => [
            ['name' => 'Dinner Seat', 'price' => 150, 'quantity' => 300],
            ['name' => 'VIP Table', 'price' => 1000, 'quantity' => 20],
        ]
    ],
    [
        'title' => 'Startup Hackathon',
        'venue' => 'Tech Hub',
        'location' => 'Berlin, Germany',
        'category' => 'Technology',
        'tickets' => [
            ['name' => 'Participant Pass', 'price' => 50, 'quantity' => 300],
            ['name' => 'Mentor Pass', 'price' => 0, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'Wine & Dine Gala',
        'venue' => 'Grand Ballroom',
        'location' => 'Los Angeles, USA',
        'category' => 'Food & Wine',
        'tickets' => [
            ['name' => 'Dinner Seat', 'price' => 200, 'quantity' => 200],
            ['name' => 'VIP Wine Pairing', 'price' => 500, 'quantity' => 50],
        ]
    ],
    [
        'title' => 'Sports Legends Meet',
        'venue' => 'National Stadium',
        'location' => 'Madrid, Spain',
        'category' => 'Sports',
        'tickets' => [
            ['name' => 'Fan Pass', 'price' => 40, 'quantity' => 1000],
            ['name' => 'VIP Meet', 'price' => 300, 'quantity' => 100],
        ]
    ],
    [
        'title' => 'Art Auction Evening',
        'venue' => 'Gallery Hall',
        'location' => 'Rome, Italy',
        'category' => 'Art & Culture',
        'tickets' => [
            ['name' => 'Entry Pass', 'price' => 60, 'quantity' => 200],
            ['name' => 'VIP Auction', 'price' => 500, 'quantity' => 50],
        ]
    ],
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