<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorHotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // 'location' => $this->city . ', ' . $this->country,
            'image' => $this->images->where('is_primary', true)->first()
                ? $this->images->where('is_primary', true)->first()->image_path
                : null,

            'thumbnail' => $this->images->where('is_primary', true)->first()?->image_path ?? $this->images->first()?->image_path,
            // We use the RoomTypeResource to ensure standard formatting
            'room_tiers' => RoomTypeResource::collection($this->whenLoaded('roomTypes')),
            'gallery' => $this->images->isNotEmpty() ? $this->images->map(fn($img) => [
                'id' => $img->id,
                'url' => $img->image_path,
                'is_primary' => (bool) $img->is_primary
            ]) : null,

            'location' => [
                'country' => $this->location?->country,
                'city' => $this->location?->city,
                'full_address' => $this->location?->full_address,
                'zip_code' => $this->location?->zip_code,
                'latitude' => $this->location?->latitude ? (float) $this->location->latitude : null,
                'longitude' => $this->location?->longitude ? (float) $this->location->longitude : null,
            ],

            'amenities' => $this->amenities->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'icon' => $a->icon,
                'slug' => $a->slug
            ]),

            // 2. Simple string for the UI Card (e.g., "Male, Maldives")
            'location_summary' => "{$this->location?->city}, {$this->location?->country}",

            'stars' => $this->star_rating,
            'status' => $this->status, // active, pending, inactive

            // Logic: Get the cheapest room price for this building
            'pricePerNight' => (float) ($this->room_types_min_base_price ?? 0),

            // Stats: Aggregated from the Controller
            'bookings' => (int) ($this->bookings_count ?? 0),
            'revenue' => (float) ($this->bookings_sum_total_price ?? 0),

            // Ratings (Assuming Review system exists)
            'rating' => round($this->reviews_avg_rating ?? 0, 1),
            'reviews' => (int) ($this->reviews_count ?? 0),

            'createdAt' => $this->created_at->format('Y-m-d'),
        ];
    }
}
