<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\Vendor\VendorEventResource;
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

    public function index()
    {
        $events = auth()->user()->events()
            ->with(['tickets', 'category', 'images'])
            ->latest()
            ->get();

        return $this->successResponse(VendorEventResource::collection($events));
    }

    /**
     * Update the specified event.
     * Supports Partial Updates (sending only the fields that changed).
     */
    public function update(Request $request, Event $event)
    {
        // 1. Security: Ensure the vendor owns this event
        if ($event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized access to this event.'], 403);
        }

        // return response()->json([
        //     'received_data' => $request->all(),
        //     'method' => $request->method(),
        // ]);

        // 2. Validation: 'sometimes' means validate ONLY if the key exists in the request
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:event_categories,id',
            'description' => 'sometimes|nullable|string',
            'startDate' => 'sometimes|required|date',
            'startTime' => 'sometimes|required',
            'endDate' => 'sometimes|required|date',
            'endTime' => 'sometimes|required',
            'venue' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'visibility' => 'sometimes|required|in:public,private',
            'status' => 'sometimes|required|in:active,draft,cancelled',
            'image' => 'sometimes|nullable|image|max:5120',

            // Ticket Validation
            'tickets' => 'sometimes|required|array|min:1',
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric|min:0',
            'tickets.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $event) {

            // 3. Prepare Data for Update
            $dataToUpdate = $request->only([
                'title', 'description', 'category_id', 'venue', 'location', 'visibility', 'status',
            ]);

            // 4. Logic: Handle Date/Time Updates
            // If any part of the time changed, we rebuild the Carbon objects
            if ($request->hasAny(['startDate', 'startTime', 'endDate', 'endTime'])) {
                $newStart = Carbon::parse(
                    ($request->startDate ?? $event->start_time->format('Y-m-d')).' '.
                    ($request->startTime ?? $event->start_time->format('H:i'))
                );

                $newEnd = Carbon::parse(
                    ($request->endDate ?? $event->end_time->format('Y-m-d')).' '.
                    ($request->endTime ?? $event->end_time->format('H:i'))
                );

                if ($newEnd->isBefore($newStart)) {
                    return response()->json(['message' => 'End time cannot be before start time.'], 422);
                }

                $dataToUpdate['start_time'] = $newStart;
                $dataToUpdate['end_time'] = $newEnd;
            }

            // 5. Logic: Handle Image Update
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('events', 'public');

                // For MVP: We replace the primary image.
                // You can add logic here to delete the old file from storage too.
                $event->images()->delete();
                $event->images()->create([
                    'image_path' => $path,
                    'is_primary' => true,
                ]);
            }

            // 6. Logic: Handle Ticket Tiers
            if ($request->has('tickets')) {
                // TRANSACTIONAL GUARD: Don't allow ticket changes if bookings exist!
                if ($event->bookings()->whereIn('status', ['confirmed', 'completed'])->exists()) {
                    return response()->json([
                        'message' => 'Cannot modify ticket tiers once sales have been confirmed.',
                    ], 422);
                }

                // Replace old tiers with new tiers
                $event->tickets()->delete();
                foreach ($request->tickets as $ticketData) {
                    $event->tickets()->create($ticketData);
                }

                // Update the derived event stats
                $dataToUpdate['total_capacity'] = collect($request->tickets)->sum('quantity');
                $dataToUpdate['base_price'] = collect($request->tickets)->min('price');
            }

            // 7. Finalize Update
            $event->update($dataToUpdate);

            return $this->successResponse(
                new VendorEventResource($event->load(['tickets', 'category', 'images'])),
                'Event updated successfully.'
            );
        });
    }

    // 3. Delete Event
    public function destroy(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Business Logic: Don't allow deletion if tickets are already sold!
        if ($event->bookings()->exists()) {
            return response()->json([
                'message' => 'Cannot delete event with existing bookings. Try cancelling it instead.',
            ], 422);
        }

        $event->delete();

        return $this->successResponse(null, 'Event deleted successfully');
    }

    public function show(Event $event)
    {
        // Security: Ensure the vendor owns this event
        if ($event->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Return using the Vendor Resource so all fields are available
        return $this->successResponse(
            new VendorEventResource($event->load(['tickets', 'category', 'images']))
        );
    }
}
