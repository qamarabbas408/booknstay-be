<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mainImage = $this->images->where('is_primary', true)->first() ?? $this->images->first();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'stars' => $this->star_rating,
            'pricePerNight' => (float) $this->base_price,

            // 1. Location details
            'location' => [
                'city' => $this->location?->city,
                'country' => $this->location?->country,
                'full_address' => $this->location?->full_address,
                'lat' => $this->location?->latitude,
                'lng' => $this->location?->longitude,
            ],

            // 2. Public Room Tiers
            // Logic: Only show 'active' rooms to guests
            'room_tiers' => $this->roomTypes->where('status', 'active')->map(fn($room) => [
                'id' => $room->id,
                'name' => $room->name,
                'price' => (float) $room->base_price,
                'max_guests' => $room->max_occupancy,
                'description' => $room->description,
            ])->values(), // values() resets the array keys after filtering

            // 3. Overall pricing logic for search results
            'starting_price' => (float) $this->roomTypes->where('status', 'active')->min('base_price'),

            // 4. Media & Ratings
            'image' => $mainImage ?  $mainImage->image_path : null,
            'rating' => 4.8,
            'reviewCount' => 120,

            'amenities' => $this->amenities->map(fn($a) => [
                'name' => $a->name,
                'icon' => $a->icon
            ]),
            'reviews'=> 120
        ];
    }

//    public function toArray(Request $request): array
//    {
//        return [
//            'id' => $this->id,
//            'name' => $this->name,
//            'location' => $this->city.', '.$this->country,
//            'descripton' => $this->description,
//            // 'pricePerNight
//            'pricePerNight' => (float) $this->base_price,
//            // We take the first image or a placeholder
//            'image' => $this->images->where('is_primary', true)->first()?->image_path
//                       ?? null,
//            'stars' => 5, // Hardcoded for now until we add star logic
//            'rating' => 4.8, // Hardcoded until we build the Reviews API
//            'reviewCount' => 120,
//            'featured' => $this->status === 'active',
//            'amenities' => $this->amenities->pluck('slug')->toArray(),
//            'stars' => $this->star_rating, // 3, 4, or 5 (Matches your UI stars)
//            // Community Rating (Matches your UI 4.9, 4.8 etc)
//            'rating' => $this->average_rating,
//            'reviewCount' => $this->review_count,
//        ];
//    }
}
