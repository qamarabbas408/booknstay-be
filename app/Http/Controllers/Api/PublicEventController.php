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
}