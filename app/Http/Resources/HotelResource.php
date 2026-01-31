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
            'pricePerNight' => (float) $this->base_price,
            // We take the first image or a placeholder
            'image' => $this->images->where('is_primary', true)->first()?->image_path
                       ?? 'https://via.placeholder.com/800x600?text=No+Image',
            'stars' => 5, // Hardcoded for now until we add star logic
            'rating' => 4.8, // Hardcoded until we build the Reviews API
            'reviewCount' => 120,
            'featured' => $this->status === 'active',
            'amenities' => ['wifi', 'pool'], // Dummy for now
        ];
    }
}
