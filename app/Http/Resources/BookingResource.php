<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isEvent = $this->bookable_type === 'App\Models\Event';
        $bookable = $this->bookable;

        // Logic: Calculate nights for hotel math (V2)
        $nights = !$isEvent && $this->check_in && $this->check_out
            ? $this->check_in->diffInDays($this->check_out)
            : 1;

        return [
            'id' => $this->id,
            'type' => $isEvent ? 'event' : 'hotel',
            'bookingCode' => $this->booking_code,
            'status' => $this->status,
            'title' => $isEvent ? $bookable->title : $bookable->name,

            // 1. Location Logic (V2 Location Table support)
            // Inside app/Http/Resources/BookingResource.php

            'location' => $bookable->location
                ? "{$bookable->location->city}, {$bookable->location->country}"
                : $bookable->location,

            // 2. Financial Breakdown (Matches your new request)
            'priceDetails' => [
                'totalPaid' => (float) $this->total_price,
                'taxRate' => $isEvent ? 0 : (float) $bookable->tax_rate,
                'servicePrice' => $isEvent ? 0 : (float) $bookable->service_fee,
                'nights' => $isEvent ? null : $nights,
            ],

            // 3. Room / Ticket Details (Detailed info)
            'item_details' => [
                'name' => $isEvent
                    ? ($this->ticketTier?->name ?? 'Standard Admission')
                    : ($this->roomType?->name ?? 'Standard Room'),
                'description' => !$isEvent ? $this->roomType?->description : null,
            ],

            // 4. Image Handling (Full URL conversion)
            'image' => $isEvent
                ? ($bookable->image_path ? asset('storage/' . $bookable->image_path) : null)
                : ($bookable->images->where('is_primary', true)->first()
                    ? asset('storage/' . $bookable->images->where('is_primary', true)->first()->image_path)
                    : null),

            // 5. Existing String Logic (For your current UI)
            'guestsOrTickets' => $isEvent
                ? $this->tickets_count . ' ' . Str::plural('ticket', $this->tickets_count) . ' • ' . ($this->ticketTier?->name ?? 'Standard')
                : $this->guests_count . ' guests, ' . $this->rooms_count . ' rooms',

            'dates' => $isEvent
                ? $this->event_date?->format('M d, Y • g:i A')
                : $this->check_in?->format('M d') . ' – ' . $this->check_out?->format('M d, Y'),

            'checkIn' => $this->check_in?->format('M d, Y • g:i A'),
            'checkOut' => $this->check_out?->format('M d, Y • g:i A'),
            'bookedAt' => $this->created_at->format('M d, Y'),
        ];
    }
}
