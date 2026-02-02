<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorEventController extends Controller
{
    use ApiResponser;

    public function store(Request $request)
    {

        // 1. Validation
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:event_categories,id',
            'description' => 'required|string|min:10',
            'startDate' => 'required|date',
            'startTime' => 'required',
            'endDate' => 'required|date|after_or_equal:startDate', // Basic date check
            'endTime' => 'required',
            'venue' => 'required|string',
            'location' => 'required|string',
            'visibility' => 'required|in:public,private',
            'status' => 'required|in:active,draft',
            // 'image' => 'nullable|image|max:5120', // 5MB Max
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048', // Each image max 2MB

            // Nested Tickets Validation
            'tickets' => 'required|array|min:1',
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {

            // 1. Logic: Combine Start Date/Time and End Date/Time
            $startFull = Carbon::parse($request->startDate.' '.$request->startTime);
            $endFull = Carbon::parse($request->endDate.' '.$request->endTime);

            // 2. Business Logic Guard:
            // Ensure the full timestamp of END is after START
            if ($endFull->isBefore($startFull)) {
                return response()->json([
                    'message' => 'The end time must be after the start time.',
                ], 422);
            }

            // 3. Logic: Calculate derived data
            $totalCapacity = collect($request->tickets)->sum('quantity');
            $basePrice = collect($request->tickets)->min('price');

            // 4. Handle Image Upload
            $imagePath = $request->hasFile('image')
                ? $request->file('image')->store('events', 'public')
                : null;

            // 5. Create Event
            $event = Event::create([
                'user_id' => auth()->id(),
                'event_category_id' => $request->category_id,
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'venue' => $request->venue,
                'start_time' => $startFull, // Stored as datetime
                'end_time' => $endFull,     // Stored as datetime
                'base_price' => $basePrice,
                'total_capacity' => $totalCapacity,
                'image_path' => $imagePath,
                'visibility' => $request->visibility,
                'status' => $request->status,
            ]);

            // 2. Handle Multiple Images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('events', 'public');

                    $event->images()->create([
                        'image_path' => $path,
                        'is_primary' => ($index === 0), // First one is the banner
                    ]);
                }
            }

            // 6. Create Ticket Tiers
            foreach ($request->tickets as $ticket) {
                $event->tickets()->create([
                    'name' => $ticket['name'],
                    'price' => $ticket['price'],
                    'quantity' => $ticket['quantity'],
                ]);
            }

            return $this->successResponse(
                new EventResource($event->load('images', 'tickets')),
                'Multi-day event created successfully!',
                201
            );
            // 7. Return using Resource
            // return $this->successResponse(
            //     new EventResource($event->load('tickets', 'category')),
            //     'Event and tickets created successfully!',
            //     201
            // );
        });
    }
}
