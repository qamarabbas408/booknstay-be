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
            // Stats for Dashboard
            'total_capacity' => $this->total_capacity,
            'tickets_sold' => $this->tickets()->sum('sold'),
            'revenue' => (float) $this->bookings()->where('status', 'confirmed')->sum('total_price'),

            // Relationships
            'category' => $this->category?->name,
            'tickets' => $this->tickets, // Includes tiers and prices
            'images' => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => asset('storage/'.$img->image_path),
                'is_primary' => $img->is_primary,
            ]),
        ];
    }
}
