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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category->name,
            'location' => $this->location,
            'venue' => $this->venue,
            'price' => '$'.number_format($this->base_price, 0),
            'date' => $this->start_time->format('M d, Y'),
            'time' => $this->start_time->format('g:i A'),
            'image' => $this->image_path ?? 'https://via.placeholder.com/800x600',
            'rating' => 4.8, // Dummy until Review logic
            'attendees' => 1200, // Dummy until Ticket sales logic
            'featured' => (bool) $this->is_featured,
            'trending' => (bool) $this->is_trending,
        ];
    }
}
