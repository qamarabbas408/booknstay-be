<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
 public function toArray(Request $request): array
{
    $isEvent = $this->bookable_type === 'App\Models\Event';

    return [
        'id' => $this->id,
        'type' => $isEvent ? 'event' : 'hotel',
        'title' => $isEvent ? $this->bookable->title : $this->bookable->name,
        'location' => $this->bookable->location,
        'status' => $this->status,
        'price' => (float) $this->total_price,
        'bookingCode' => $this->booking_code,
        'image' => $isEvent 
            ?  $this->bookable->image_path
            : $this->bookable->images->where('is_primary', true)->first()?->image_path,
        
        // Custom string logic for your UI
         'guestsOrTickets' => $isEvent 
            ? $this->tickets_count . ' ' . Str::plural('ticket', $this->tickets_count) . ' • ' . ($this->ticketTier?->name ?? 'Standard')
            : $this->guests_count . ' guests, ' . $this->rooms_count . ' rooms',
        'dates' => $isEvent 
            ? $this->event_date?->format('M d, Y • g:i A')
            : $this->check_in?->format('M d') . ' – ' . $this->check_out?->format('M d, Y'),
        
        'checkIn' => $this->check_in?->format('M d, Y • g:i A'),
        'checkOut' => $this->check_out?->format('M d, Y • g:i A'),
        
        // Created timestamp
        'bookedAt' => $this->created_at->format('M d, Y'),
    ];
}
}
