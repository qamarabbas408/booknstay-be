<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorEventResource extends JsonResource
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
            'status' => $this->status, // active, draft, cancelled
            'visibility' => $this->visibility,
            'start_date' => $this->start_time?->format('Y-m-d H:i'),
            'end_date' => $this->end_time?->format('Y-m-d H:i'),
            'description'=>$this->description, 
            'location' => $this->location,
            'venue' => $this->venue,
            // Stats for Dashboard
            'total_capacity' => $this->total_capacity,
            'tickets_sold' => $this->tickets()->sum('sold'),
            'revenue' => (float) $this->bookings()->where('status', 'confirmed')->sum('total_price'),
            'highlights'=>$this->highlights,
            // Relationships
            'category' => $this->category?->name,
            // 'tickets' => $this->tickets, // Includes tiers and prices
            'images' => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => asset('storage/'.$img->image_path),
                'is_primary' => $img->is_primary,
            ]),
            'tickets' => $this->tickets->map(function($ticket) {
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => (float) $ticket->price,
                'quantity' => $ticket->quantity,
                // BUSINESS LOGIC: 
                // A tier is NOT editable if it has confirmed or completed bookings
                'is_locked' => $ticket->bookings()
                                     ->whereIn('status', ['confirmed', 'completed'])
                                     ->exists(),
                'sold_count' => $ticket->bookings()->count(),
                'features' => $ticket->features ?? [], 

            ];
        }),
        ];
    }
}
