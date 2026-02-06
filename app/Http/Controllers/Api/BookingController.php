<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Hotel;
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
    public function show($id)
    {
        // 1. Fetch the booking but ensure it belongs to the logged-in user
        // We eager load 'bookable' (Hotel/Event) and 'ticketTier'
        $booking = auth()->user()->bookings()
            ->with(['bookable.images', 'ticketTier'])
            ->findOrFail($id);

        // 2. Return using the resource
        return $this->successResponse(new BookingResource($booking));
    }

    public function storeHotelBooking(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'check_in' => 'required|date|after:yesterday',
            'check_out' => 'required|date|after:check_in', // This forces at least +1 day
            'guests_count' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $hotel = Hotel::findOrFail($request->hotel_id);

            // 1. Calculate Nights
            $checkIn = \Carbon\Carbon::parse($request->check_in);
            $checkOut = \Carbon\Carbon::parse($request->check_out);
            $nights = $checkIn->diffInDays($checkOut);

            // 2. Business Logic: Even if 0 days difference, charge for at least 1 night
            $billableNights = max(1, $nights);
            // 2. Pricing Logic
            $subtotal = $hotel->base_price * $billableNights;

            $serviceFee = $subtotal * 0.10; // 10% marketplace fee
            $totalPrice = $subtotal + $serviceFee;


            // 1. Find all bookings that overlap with the requested dates
            $overlappingBookingsCount = Booking::where('bookable_type', Hotel::class)
                ->where('bookable_id', $hotel->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut->subMinute()])
                        ->orWhereBetween('check_out', [$checkIn->addMinute(), $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })
                ->sum('rooms_count'); // Total rooms already taken


            // 2. Inventory Guard
            $roomsRequested = $request->rooms_count ?? 1;
            $roomsAvailable = $hotel->total_rooms - $overlappingBookingsCount;

            if ($roomsAvailable < $roomsRequested) {
                return response()->json([
                    'message' => "Sold out! Only {$roomsAvailable} rooms left for these dates."
                ], 422);
            }

            // 3. Create Booking (Using the polymorphic table)
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'bookable_id' => $hotel->id,
                'bookable_type' => Hotel::class,
                'booking_code' => 'BNS-H-' . strtoupper(Str::random(8)),
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'guests_count' => $request->guests_count,
                'rooms_count' => $roomsRequested, // Default for now
                'total_price' => $totalPrice,
                'status' => 'confirmed',
            ]);

            return $this->successResponse($booking, 'Hotel reserved successfully!');
        });
    }

}
