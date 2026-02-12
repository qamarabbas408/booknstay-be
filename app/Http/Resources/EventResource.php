<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Logic: Find the primary banner image
        $mainImage = $this->images->where('is_primary', true)->first() ?? $this->images->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'highlights' => $this->highlights ?? [], // Array of strings

            // 1. V2 Location Mapping
            'location_details' => [
//                'venue' => $this->venue,
                'address' => $this->location?->full_address,
                'city' => $this->location?->city,
                'country' => $this->location?->country,
                'lat' => (float) $this->location?->latitude,
                'lng' => (float) $this->location?->longitude,
            ],

            // 2. Timing
            'date' => $this->start_time?->format('F d, Y'),
            'time' => $this->start_time?->format('g:i A') . ' – ' . $this->end_time?->format('g:i A'),
            'is_past' => $this->is_past,

            // 3. Ticket Tiers (The "Products")
            'ticketTypes' => $this->tickets->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'price' => (float) $t->price,
                'features' => $t->features ?? [], // Array of strings
                'available' => $t->quantity - $t->sold,
                'soldOut' => ($t->quantity - $t->sold) <= 0,
            ]),

            // 4. Media
            'image' => $mainImage ? asset('storage/' . $mainImage->image_path) : null,
            'gallery' => $this->images->map(fn($img) => asset('storage/' . $img->image_path)),

            // 5. Social Proof
            'category' => $this->category?->name,
            'rating' => 4.8, // Static for now
            'attendees' => $this->tickets->sum('sold') . ' going',
        ];
    }
}
