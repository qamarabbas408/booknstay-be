<?php

namespace App\Http\Resources\Vendor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomTypeResource extends JsonResource
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
            'description' => $this->description,
            'base_price' => (float) $this->base_price,
            'max_occupancy' => $this->max_occupancy,
            'total_inventory' => $this->total_inventory,

            // Business Logic: Lock the room if people have already booked it
            'is_locked' => $this->bookings()->whereIn('status', ['confirmed', 'completed'])->exists(),
            'active_bookings_count' => $this->bookings()->whereIn('status', ['confirmed'])->count(),
        ];
    }
}
