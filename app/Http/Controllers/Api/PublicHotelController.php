<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use App\Traits\ApiResponser;
use Illuminate\Http\Request; // Import the trait

class PublicHotelController extends Controller
{
    //
    use ApiResponser; // Use the trait here

    public function index(Request $request)
    {
        // 1. Start the query on Active hotels
        $query = Hotel::where('status', 'active')->with(['images', 'amenities']);

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
}
