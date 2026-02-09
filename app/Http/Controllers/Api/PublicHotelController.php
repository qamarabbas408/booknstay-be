<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Traits\ApiResponser;
use Illuminate\Http\Request; // Import the trait
use App\Models\Booking;
class PublicHotelController extends Controller
{
    //
    use ApiResponser; // Use the trait here

public function checkAvailability(Request $request, $id)
{
    // 1. Validate Input
    $request->validate([
        'check_in' => 'required|date|after:yesterday',
        'check_out' => 'required|date|after:check_in',
    ]);

    $hotel = Hotel::findOrFail($id);
    $checkIn = \Carbon\Carbon::parse($request->check_in);
    $checkOut = \Carbon\Carbon::parse($request->check_out);

    // 2. Calculate Occupied Rooms
    // Logic: Find all bookings where (Start < RequestedEnd) AND (End > RequestedStart)
    $occupiedRooms = Booking::where('bookable_type', Hotel::class)
        ->where('bookable_id', $hotel->id)
        ->whereIn('status', ['confirmed', 'completed'])
        ->where(function ($query) use ($checkIn, $checkOut) {
            $query->where('check_in', '<', $checkOut)
                  ->where('check_out', '>', $checkIn);
        })
        ->sum('rooms_count');

    // 3. Calculate Availability
    $availableRooms = max(0, $hotel->total_rooms - $occupiedRooms);

    return $this->successResponse([
        'hotel_id' => $hotel->id,
        'total_rooms' => $hotel->total_rooms,
        'occupied_rooms' => (int)$occupiedRooms,
        'available_rooms' => (int)$availableRooms,
        'is_available' => $availableRooms > 0,
    ], 'Availability checked successfully');
}
    public function index(Request $request)
    {
        // 1. Start the query on Active hotels
//        $query = Hotel::where('status', 'active')->with(['images', 'amenities', 'reviews']);

        $query = Hotel::where('status', 'active')
            ->with(['location', 'images', 'amenities','reviews','roomTypes' => function($q) {
                // Eager load only active room tiers
                $q->where('status', 'active');
            }]);

        // 2. SEARCH (By Name or City)
        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('city', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('country', 'LIKE', "%{$searchTerm}%");
            });
        }

        // 3. FILTER (By Price)
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->query('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->query('max_price'));
        }

        // 2. AMENITIES FILTERING (AND Logic)
        if ($request->has('amenities') && is_array($request->amenities)) {
            foreach ($request->amenities as $amenitySlug) {
                // We loop through each selected amenity and ensure the hotel has IT.
                // This ensures a hotel must have ALL selected amenities.
                $query->whereHas('amenities', function ($q) use ($amenitySlug) {
                    $q->where('slug', $amenitySlug);
                });
            }
        }

        // STAR RATING FILTER (Luxury, Upscale, Comfort)
        if ($request->has('stars') && is_array($request->stars)) {
            // e.g. ?stars[]=5&stars[]=4
            $query->whereIn('star_rating', $request->stars);
        }

        // 4. SORTING
        $sortField = 'created_at';
        $sortDirection = 'desc';

        if ($request->query('sort_by') === 'price_low') {
            $sortField = 'base_price';
            $sortDirection = 'asc';
        } elseif ($request->query('sort_by') === 'price_high') {
            $sortField = 'base_price';
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        // 5. PAGINATION (Standardizing the Limit)
        // $limit = $request->query('limit', 12); // Default to 12 if not provided

        $hotels = $query->paginate($request->query('limit', 12));

        // 6. Return using the Resource
        // return HotelResource::collection($hotels);
        return $this->paginatedResponse(
            $hotels,
            HotelResource::collection($hotels)
        );
    }


    public function show($id)
    {
        // Eager load everything needed for the UI
        $hotel = Hotel::with(['images', 'amenities', 'reviews.user'])
            ->where('status', 'active')
            ->findOrFail($id);

        return $this->successResponse(new HotelResource($hotel));
    }


}
