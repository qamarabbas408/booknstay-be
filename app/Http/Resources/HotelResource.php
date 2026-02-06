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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->city.', '.$this->country,
            'descripton' => $this->description,
            // 'pricePerNight
            'pricePerNight' => (float) $this->base_price,
            // We take the first image or a placeholder
            'image' => $this->images->where('is_primary', true)->first()?->image_path
                       ?? null,
            'stars' => 5, // Hardcoded for now until we add star logic
            'rating' => 4.8, // Hardcoded until we build the Reviews API
            'reviewCount' => 120,
            'featured' => $this->status === 'active',
            'amenities' => $this->amenities->pluck('slug')->toArray(),
            'stars' => $this->star_rating, // 3, 4, or 5 (Matches your UI stars)
            // Community Rating (Matches your UI 4.9, 4.8 etc)
            'rating' => $this->average_rating,
            'reviewCount' => $this->review_count,
        ];
    }
}
