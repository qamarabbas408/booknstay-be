<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\VendorEventResource;
use App\Models\Event;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorEventController extends Controller
{
    //
    use ApiResponser;
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            // Location Validation
            'country' => 'required|string',
            'city' => 'required|string',
            'full_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',

            // Other Validations
            'category_id' => 'required|exists:event_categories,id',
            'description' => 'required|string|min:10',
            'startDate' => 'required|date',
            'startTime' => 'required',
            'endDate' => 'required|date|after_or_equal:startDate', // Basic date check
            'endTime' => 'required',

            'visibility' => 'required|in:public,private',
            'status' => 'required|in:active,draft',

            // 'image' => 'nullable|image|max:5120', // 5MB Max
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048', // Each image max 2MB
            'highlights' => 'nullable|array', // Validate as array

            // Nested Tickets Validation
            'tickets' => 'required|array|min:1',
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1',

            'tickets.*.features' => 'nullable|array', // Validate each ticket's features
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create the Event (we leave the 'location' column empty)
            // 1. Logic: Combine Start Date/Time and End Date/Time
            $startFull = Carbon::parse($request->startDate . ' ' . $request->startTime);
            $endFull = Carbon::parse($request->endDate . ' ' . $request->endTime);

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
                'start_time' => $startFull, // Stored as datetime
                'end_time' => $endFull,     // Stored as datetime
                'base_price' => $basePrice,
                'total_capacity' => $totalCapacity,
                'image_path' => $imagePath,
                'visibility' => $request->visibility,
                'status' => $request->status,
                'highlights' => $request->highlights, // Laravel converts array to JSON automatically

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
                    'features' => $ticket['features'] ?? [], // Save the features array

                ]);
            }


            // 2. Create the Location record
            $event->location()->create([
                'country' => $request->country,
                'city' => $request->city,
                'full_address' => $request->full_address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            return $this->successResponse($event->load('location'), 'Event created!');
        });
    }

    public function update(Request $request, Event $event)
    {
        // 1. Security Check
        if ($event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Validation
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:event_categories,id',
            'description' => 'sometimes|nullable|string',
            'highlights' => 'sometimes|array',

            // Location Validation
            'country' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'full_address' => 'sometimes|required|string',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',

            // Tickets Validation
            'tickets' => 'sometimes|array|min:1',
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric',
            'tickets.*.quantity' => 'required|integer',
            'tickets.*.features' => 'nullable|array',
        ]);
//dd($request);
        return DB::transaction(function () use ($request, $event) {

            // 3. Update Basic Event Data
            $eventData = $request->only(['title', 'description', 'category_id', 'status', 'visibility', 'highlights', 'venue']);

            // 4. Handle Date/Time logic (Combining inputs)
            if ($request->hasAny(['startDate', 'startTime', 'endDate', 'endTime'])) {
                $start = Carbon::parse(($request->startDate ?? $event->start_time->format('Y-m-d')) . ' ' . ($request->startTime ?? $event->start_time->format('H:i')));
                $end = Carbon::parse(($request->endDate ?? $event->end_time->format('Y-m-d')) . ' ' . ($request->endTime ?? $event->end_time->format('H:i')));

                $eventData['start_time'] = $start;
                $eventData['end_time'] = $end;
            }

            $event->update($eventData);

            // 5. Update Location (location relationship)
            if ($request->hasAny(['country', 'city', 'full_address', 'latitude', 'longitude'])) {
                $event->location()->update($request->only([
                    'country', 'city', 'full_address', 'latitude', 'longitude'
                ]));
            }

            // 6. Handle Image Gallery
            if ($request->hasFile('images')) {
                // Logic: For simplicity, we add new images to the gallery
                foreach ($request->file('images') as $image) {
                    $path = $image->store('events', 'public');
                    $event->images()->create(['image_path' => $path]);
                }
            }

            // 7. Handle Tickets (with Sales Guard)
            if ($request->has('tickets')) {
                // BUSINESS RULE: Cannot edit tickets if bookings already exist
                $hasSales = $event->bookings()->whereIn('status', ['confirmed', 'completed'])->exists();

                if ($hasSales) {
                    return response()->json(['message' => 'Cannot modify tickets because sales have already started.'], 422);
                }

                // Sync Tickets: Delete old and create new
                $event->tickets()->delete();
                foreach ($request->tickets as $ticketData) {
                    $event->tickets()->create([
                        'name' => $ticketData['name'],
                        'price' => $ticketData['price'],
                        'quantity' => $ticketData['quantity'],
                        'features' => $ticketData['features'] ?? [],
                    ]);
                }

                // Update Event Aggregate stats
                $event->update([
                    'base_price' => collect($request->tickets)->min('price'),
                    'total_capacity' => collect($request->tickets)->sum('quantity'),
                ]);
            }


            return $this->successResponse(
                new VendorEventResource($event->load(['location', 'tickets', 'images'])),
                'Event updated successfully'
            );
        });
    }
}
