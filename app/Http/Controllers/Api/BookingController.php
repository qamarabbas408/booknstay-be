<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser; 
use App\Http\Resources\BookingResource;

class BookingController extends Controller
{
    use ApiResponser; 

     /**
     * Get the logged-in guest's bookings.
     */
    public function index(Request $request)
    {
        // 1. Get query on the current user's bookings
        $query = auth()->user()->bookings()
            ->with(['bookable', 'ticketTier']) // Eager load the Hotel/Event and the Tier
            ->latest();
            
        // 2. Filter by Tab (Upcoming vs Past)
        $tab = $request->query('tab', 'upcoming');

        if ($tab === 'upcoming') {
            $query->whereIn('status', ['confirmed', 'pending']);
        } else {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        // 3. Optional: Filter by Type (hotel or event)
        if ($request->has('type') && $request->type !== 'all') {
            $typeClass = $request->type === 'hotel' ? 'App\Models\Hotel' : 'App\Models\Event';
            $query->where('bookable_type', $typeClass);
        }

        $bookings = $query->paginate($request->query('limit', 10));

        return $this->paginatedResponse(
            $bookings, 
            BookingResource::collection($bookings)
        );
    }
}
