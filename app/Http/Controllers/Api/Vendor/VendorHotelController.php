<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Resources\Vendor\VendorHotelResource;
use Illuminate\Support\Facades\DB;

class VendorHotelController extends Controller
{
    //
    use ApiResponser;
    public function index(Request $request)
    {
        // 1. Start the query on the authenticated vendor's hotels
        $query = auth()->user()->hotels()
            ->with(['images'])
            ->withMin('roomTypes', 'base_price') // Cheapest room price
            ->withCount([
                'bookings' => function ($q) {
                    $q->whereIn('status', ['confirmed', 'completed']);
                }
            ])
            ->withSum([
                'bookings' => function ($q) {
                    $q->whereIn('status', ['confirmed', 'completed']);
                }
            ], 'total_price')
            ->withCount(relations: 'reviews')
            ->withAvg('reviews', 'rating');

        // 2. FILTER: By Status (active, pending, inactive)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 3. SEARCH: By Name or City
        if ($request->has('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        // 4. SORTING
        $sortBy = $request->query('sort_by', 'recent');
        if ($sortBy === 'price_high') {
            $query->orderBy('room_types_min_base_price', 'desc');
        } elseif ($sortBy === 'price_low') {
            $query->orderBy('room_types_min_base_price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 5. PAGINATION & LIMIT
        $limit = $request->query('limit', 10);
        $hotels = $query->paginate($limit);

        // 6. Return using your custom paginatedResponse trait
        return $this->paginatedResponse(
            $hotels,
            VendorHotelResource::collection($hotels)
        );
    }
    public function store(Request $request)
    {
        // 1. Validation including new GPS/Map fields
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'star_rating' => 'required|integer|min:3|max:5',

            // Location Fields
            'country' => 'required|string',
            'city' => 'required|string',
            'full_address' => 'required|string',
            'zip_code' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            // GALLERY VALIDATION: Force minimum 5, allow up to 10
            'images' => 'required|array|min:5|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB per image

            // NEW: Amenities (Array of IDs from your master list)
            'amenities' => 'required|array|min:1',
            'amenities.*' => 'exists:amenities,id',
        ]);

        return DB::transaction(function () use ($request) {

            // 2. Create the Hotel building
            $hotel = auth()->user()->hotels()->create([
                'name' => $request->name,
                'description' => $request->description,
                'star_rating' => $request->star_rating,
                'status' => 'pending',
            ]);

            // 3. Create the Physical Location (Polymorphic link)
            $hotel->location()->create([
                'country' => $request->country,
                'city' => $request->city,
                'full_address' => $request->full_address,
                'zip_code' => $request->zip_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            // 3. Link Amenities (The Many-to-Many "Pivot" table)
            $hotel->amenities()->attach($request->amenities);

            // 3. Handle 5+ Images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('hotels', 'public');

                    $hotel->images()->create([
                        'image_path' => $path,
                        // The first image (index 0) is the Main Banner
                        'is_primary' => ($index === 0),
                    ]);
                }
            }

            return $this->successResponse(
                $hotel->load('location', 'images'),
                'Hotel registered with location data!'
            );
        });
    }
}
