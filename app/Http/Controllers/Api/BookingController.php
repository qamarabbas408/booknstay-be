<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser; 
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    use ApiResponser; 

     /**
     * Get the logged-in guest's bookings.
     */
    public function index(Request $request)
    {
        // 1. Get query on the current user's bookings
        $query = auth()->user()->bookings()
            ->with(['bookable', 'ticketTier']) // Eager load the Hotel/Event and the Tier
            ->latest();
            
        // 2. Filter by Tab (Upcoming vs Past)
        $tab = $request->query('tab', 'upcoming');

        if ($tab === 'upcoming') {
            $query->whereIn('status', ['confirmed', 'pending']);
        } else {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        // 3. Optional: Filter by Type (hotel or event)
        if ($request->has('type') && $request->type !== 'all') {
            $typeClass = $request->type === 'hotel' ? 'App\Models\Hotel' : 'App\Models\Event';
            $query->where('bookable_type', $typeClass);
        }

        $bookings = $query->paginate($request->query('limit', 10));

        return $this->paginatedResponse(
            $bookings, 
            BookingResource::collection($bookings)
        );
    }

    public function storeEventBooking(Request $request)
{
    $request->validate([
        'event_id' => 'required|exists:events,id',
        'selections' => 'required|array|min:1',
        'selections.*.ticket_id' => 'required|exists:event_tickets,id',
        'selections.*.quantity' => 'required|integer|min:1|max:10',
    ]);

    return DB::transaction(function () use ($request) {
        $event = Event::findOrFail($request->event_id);
        $allBookings = [];
        $grandTotal = 0;
        $bookingCode = 'BNS-E-' . strtoupper(Str::random(8));

        foreach ($request->selections as $selection) {
            $ticketTier = EventTicket::lockForUpdate()->findOrFail($selection['ticket_id']);
            
            // 1. Inventory Check
            if (($ticketTier->quantity - $ticketTier->sold) < $selection['quantity']) {
                return response()->json(['message' => "Not enough {$ticketTier->name} tickets left."], 422);
            }

            // 2. Pricing Logic (Server-side calculation)
            $subtotal = $ticketTier->price * $selection['quantity'];
            $serviceFee = $subtotal * 0.05; // 5% fee
            $total = $subtotal + $serviceFee;
            $grandTotal += $total;

            // 3. Create Booking Record
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'bookable_id' => $event->id,
                'bookable_type' => Event::class,
                'event_ticket_id' => $ticketTier->id,
                'booking_code' => $bookingCode, // Shared code for the whole order
                'tickets_count' => $selection['quantity'],
                'total_price' => $total,
                'event_date' => $event->start_time,
                'status' => 'confirmed',
            ]);

            // 4. Deduct Inventory
            $ticketTier->increment('sold', $selection['quantity']);
            
            $allBookings[] = $booking;
        }

        return $this->successResponse([
            'order_code' => $bookingCode,
            'total_paid' => $grandTotal,
            'items' => $allBookings
        ], 'Tickets booked successfully!');
    });
}
}
