<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some vendor users (assuming role = 'vendor')
        $vendors = User::where('role', 'vendor')->get();
        if ($vendors->isEmpty()) {
            $this->command->error('No vendors found. Please run UserSeeder first.');

            return;
        }

        // Get categories
        $categories = EventCategory::all();
        if ($categories->isEmpty()) {
            $this->command->error('No event categories found. Please run EventCategorySeeder first.');

            return;
        }

        $sampleEvents = [
            [
                'title' => 'Summer Music Festival',
                'description' => 'An outdoor festival featuring live bands, food trucks, and art installations.',
                'location' => 'Miami, USA',
                'venue' => 'Bayfront Park',
                'start_time' => Carbon::now()->addDays(10),
                'base_price' => 50.00,
                'is_featured' => true,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/music-festival.jpg',
            ],
            [
                'title' => 'Tech Expo 2026',
                'description' => 'Showcasing the latest innovations in AI, robotics, and software development.',
                'location' => 'New York, USA',
                'venue' => 'Convention Center',
                'start_time' => Carbon::now()->addDays(30),
                'base_price' => 120.00,
                'is_featured' => true,
                'is_trending' => false,
                'status' => 'active',
                'image_path' => 'events/tech-expo.jpg',
            ],
            [
                'title' => 'Wine & Dine Gala',
                'description' => 'An elegant evening of fine wines, gourmet food, and live jazz.',
                'location' => 'Los Angeles, USA',
                'venue' => 'Grand Ballroom',
                'start_time' => Carbon::now()->addDays(45),
                'base_price' => 200.00,
                'is_featured' => false,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/wine-gala.jpg',
            ],
        ];

        $extraEvents = [
            [
                'title' => 'Startup Pitch Night',
                'description' => 'Local entrepreneurs showcase their ideas to investors and the community.',
                'location' => 'San Francisco, USA',
                'venue' => 'Innovation Hub',
                'start_time' => Carbon::now()->addDays(20),
                'base_price' => 25.00,
                'is_featured' => false,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/startup-pitch.jpg',
            ],
            [
                'title' => 'International Film Festival',
                'description' => 'Screenings of award-winning films from around the globe.',
                'location' => 'Toronto, Canada',
                'venue' => 'City Theater',
                'start_time' => Carbon::now()->addDays(60),
                'base_price' => 80.00,
                'is_featured' => true,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/film-festival.jpg',
            ],
            [
                'title' => 'Marathon for Charity',
                'description' => 'Annual marathon raising funds for local hospitals.',
                'location' => 'Boston, USA',
                'venue' => 'Downtown Streets',
                'start_time' => Carbon::now()->addDays(15),
                'base_price' => 10.00,
                'is_featured' => false,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/marathon.jpg',
            ],
            [
                'title' => 'Art Exhibition: Modern Masters',
                'description' => 'A curated collection of contemporary art pieces.',
                'location' => 'Paris, France',
                'venue' => 'Louvre Annex',
                'start_time' => Carbon::now()->addDays(40),
                'base_price' => 60.00,
                'is_featured' => true,
                'is_trending' => false,
                'status' => 'active',
                'image_path' => 'events/art-exhibition.jpg',
            ],
            [
                'title' => 'Jazz Night Under the Stars',
                'description' => 'Live jazz performances in an open-air garden.',
                'location' => 'Chicago, USA',
                'venue' => 'Millennium Park',
                'start_time' => Carbon::now()->addDays(12),
                'base_price' => 45.00,
                'is_featured' => true,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/jazz-night.jpg',
            ],
            [
                'title' => 'Gaming Convention',
                'description' => 'The latest in console, PC, and VR gaming with tournaments and demos.',
                'location' => 'Los Angeles, USA',
                'venue' => 'Expo Center',
                'start_time' => Carbon::now()->addDays(25),
                'base_price' => 100.00,
                'is_featured' => true,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/gaming-convention.jpg',
            ],
            [
                'title' => 'Fashion Week Showcase',
                'description' => 'Top designers present their new collections on the runway.',
                'location' => 'Milan, Italy',
                'venue' => 'Fashion Hall',
                'start_time' => Carbon::now()->addDays(70),
                'base_price' => 150.00,
                'is_featured' => true,
                'is_trending' => false,
                'status' => 'active',
                'image_path' => 'events/fashion-week.jpg',
            ],
            [
                'title' => 'Science & Innovation Fair',
                'description' => 'Interactive exhibits on robotics, space, and biotech.',
                'location' => 'Berlin, Germany',
                'venue' => 'Tech Arena',
                'start_time' => Carbon::now()->addDays(35),
                'base_price' => 30.00,
                'is_featured' => false,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/science-fair.jpg',
            ],
            [
                'title' => 'Literary Festival',
                'description' => 'Meet authors, attend readings, and join writing workshops.',
                'location' => 'London, UK',
                'venue' => 'Royal Hall',
                'start_time' => Carbon::now()->addDays(50),
                'base_price' => 40.00,
                'is_featured' => false,
                'is_trending' => true,
                'status' => 'active',
                'image_path' => 'events/literary-festival.jpg',
            ],
            [
                'title' => 'Wellness Retreat',
                'description' => 'Yoga, meditation, and holistic health workshops in a serene environment.',
                'location' => 'Bali, Indonesia',
                'venue' => 'Oceanfront Retreat Center',
                'start_time' => Carbon::now()->addDays(90),
                'base_price' => 250.00,
                'is_featured' => true,
                'is_trending' => false,
                'status' => 'active',
                'image_path' => 'events/wellness-retreat.jpg',
            ],
        ];

        $allEvents = array_merge($sampleEvents, $extraEvents);

        foreach ($allEvents as $eventData) {
            $vendor = $vendors->random();
            $category = $categories->random();

            Event::create(array_merge($eventData, [
                'user_id' => $vendor->id,
                'event_category_id' => $category->id,
            ]));
        }
    }
}
