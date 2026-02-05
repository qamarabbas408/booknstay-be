<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Http\Resources\EventResource;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    use ApiResponser;

    public function getCategories()
    {
        // Returns the list for the top cards in your UI
        $categories = EventCategory::withCount('events')->get();
        return $this->successResponse($categories);
    }

    public function index(Request $request)
    {
        $query = Event::where('status', 'active')->with('category');

        // Filter by Category Slug
        if ($request->has('category') && $request->category !== 'All Events') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Search by Title or Venue
        if ($request->has('search')) {
            $query->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('venue', 'LIKE', "%{$request->search}%");
        }

        $events = $query->latest()->paginate($request->query('limit', 9));

        return $this->paginatedResponse(
            $events, 
            EventResource::collection($events)
        );
    }

    public function show($id)
{
    $event = Event::with(['category', 'tickets', 'images'])->findOrFail($id);

    return $this->successResponse([
        'id' => $event->id,
        'title' => $event->title,
        'location' => $event->location,
        'venue' => $event->venue,
        'date' => $event->start_time->format('F d, Y'),
        'time' => $event->start_time->format('g:i A') . ' – ' . $event->end_time->format('g:i A'),
        'image' => $event->images->where('is_primary', true)->first()?->image_path 
                   ? asset('storage/' . $event->images->where('is_primary', true)->first()->image_path)
                   : null,
        'description' => $event->description,
        'highlights' => $event->highlights ?? [],
        'rating' => 4.8, // Calculated from reviews later
        'attendees' => $event->bookings()->sum('tickets_count') . ' going',
        'ticketTypes' => $event->tickets->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'price' => (float) $t->price,
            'available' => $t->quantity - $t->sold,
            'soldOut' => ($t->quantity - $t->sold) <= 0,
            'description' => $t->description,
            'features' => $t->features ?? [],
            'popular' => (bool) $t->is_popular
        ])
    ]);
}
}