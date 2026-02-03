<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'type' => $this->bookable_type === 'App\Models\Hotel' ? 'hotel' : 'event',
            'title' => $this->bookable->name ?? $this->bookable->title,
            'status' => $this->status,
            'bookingCode' => $this->booking_code,
            'price' => (float) $this->total_price,

            // Dynamic Logic for UI
            'details' => $this->bookable_type === 'App\Models\Event'
                ? "{$this->tickets_count} tickets • {$this->ticketTier?->name}"
                : "{$this->guests_count} guests • {$this->rooms_count} rooms",

            'dates' => $this->bookable_type === 'App\Models\Event'
                ? $this->event_date?->format('M d, Y • g:i A')
                : $this->check_in?->format('M d').' – '.$this->check_out?->format('M d, Y'),
        ];
    }
}
