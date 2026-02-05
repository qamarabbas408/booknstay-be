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
        $mainImage = $this->images->where('is_primary', true)->first() ?? $this->images->first();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category->name,
            'location' => $this->location,
            'venue' => $this->venue,
            'price' => '$' . number_format($this->base_price, 0),
            // Time Logic
            'start_date' => $this->start_time?->format('M d, Y'),
            'end_date' => $this->end_time?->format('M d, Y'),
            'is_past' => $this->is_past,
            'highlights' => $this->highlights ?? [], // Returns ["Stage 1", "Stage 2"]
            'description' => $this->description,
            // Inventory Logic
            'total_capacity' => $this->total_capacity,
            // Inventory Logic
            'tickets_left' => $this->tickets_left, // This calls getTicketsLeftAttribute() in Model
            'is_sold_out' => $this->tickets_left <= 0,

            // 'image' => $this->image_path ?? 'https://via.placeholder.com/800x600',
            'image' => $mainImage
                ? $mainImage->image_path
                : null,
            'rating' => 4.8, // Dummy until Review logic
            'attendees' => 1200, // Dummy until Ticket sales logic
            'featured' => (bool) $this->is_featured,
            'trending' => (bool) $this->is_trending,
            'ticketTypes' => $this->tickets->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'price' => (float) $t->price,
                'features' => $t->features ?? [], // Returns ["VIP Entry", "Free Drinks"]
                'available' => $t->quantity - $t->sold,
            ]),
        ];
    }
}
