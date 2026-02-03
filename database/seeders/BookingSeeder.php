<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Hotel;
use App\Models\Event;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $alice = User::where('email', 'alice@gmail.com')->first();
        $bob = User::where('email', 'bob@gmail.com')->first();
        $event = Event::with('tickets')->first(); // Get an event with its tiers
        // $event  = Event::find(15);
        $hotel = Hotel::first();

        if (!$event) {
        $this->command->error("No events with ticket tiers found. Please run EventSeeder first!");
        return;
    }
    
        // SCENARIO 1: Confirmed VIP Event Booking (High Value)
        $vipTier = $event->tickets->where('name', 'VIP')->first() ?? $event->tickets->first();
        Booking::create([
            'user_id' => $alice->id,
            'bookable_id' => $event->id,
            'bookable_type' => Event::class,
            'event_ticket_id' => $vipTier->id,
            'tickets_count' => 2,
            'total_price' => $vipTier->price * 2,
            'status' => 'confirmed',
            'event_date' => $event->start_time,
        ]);

        // SCENARIO 2: Pending General Event Booking (Abandoned Cart test)
        $genTier = $event->tickets->where('name', 'General Admission')->first() ?? $event->tickets->last();
        Booking::create([
            'user_id' => $bob->id,
            'bookable_id' => $event->id,
            'bookable_type' => Event::class,
            'event_ticket_id' => $genTier->id,
            'tickets_count' => 1,
            'total_price' => $genTier->price,
            'status' => 'pending',
            'event_date' => $event->start_time,
        ]);

        // SCENARIO 3: Completed Hotel Stay (Unlocks Review)
        Booking::create([
            'user_id' => $alice->id,
            'bookable_id' => $hotel->id,
            'bookable_type' => Hotel::class,
            'check_in' => Carbon::now()->subDays(10),
            'check_out' => Carbon::now()->subDays(5),
            'guests_count' => 2,
            'rooms_count' => 1,
            'total_price' => $hotel->base_price * 5,
            'status' => 'completed',
        ]);

        // SCENARIO 4: Cancelled Booking (Test Inventory Return)
        Booking::create([
            'user_id' => $bob->id,
            'bookable_id' => $hotel->id,
            'bookable_type' => Hotel::class,
            'check_in' => Carbon::now()->addMonths(1),
            'check_out' => Carbon::now()->addMonths(1)->addDays(3),
            'status' => 'cancelled',
            'total_price' => 500.00,
        ]);
    }
}