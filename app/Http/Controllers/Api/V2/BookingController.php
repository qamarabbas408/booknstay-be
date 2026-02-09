<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    //
    use ApiResponser;


    public function storeHotelBooking(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_type_id' => 'required|exists:room_types,id', // GUEST PICKS A TIER
            'check_in' => 'required|date|after:yesterday',
            'check_out' => 'required|date|after:check_in',
            'rooms_count' => 'required|integer|min:1',
            'guests_count' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Fetch the specific Room Type
            $roomType = RoomType::lockForUpdate()->findOrFail($request->room_type_id);

            // 2. Business Logic: Ensure this room belongs to the selected hotel
            if ($roomType->hotel_id != $request->hotel_id) {
                return response()->json(['message' => 'Invalid room selection for this hotel.'], 422);
            }

            // 3. Business Logic: Ensure room is NOT in maintenance
            if ($roomType->status !== 'active') {
                return response()->json(['message' => 'This room type is currently unavailable.'], 422);
            }

            // 4. Inventory Logic: Check availability for this specific tier
            $occupiedCount = Booking::where('room_type_id', $roomType->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->where(function ($query) use ($request) {
                    $query->where('check_in', '<', $request->check_out)
                        ->where('check_out', '>', $request->check_in);
                })
                ->sum('rooms_count');

            $available = $roomType->total_inventory - $occupiedCount;

            if ($available < $request->rooms_count) {
                return response()->json(['message' => "Only {$available} rooms of this type left."], 422);
            }

            // 5. Pricing Logic: Use the Tier Price
            $days = \Carbon\Carbon::parse($request->check_in)->diffInDays($request->check_out);
            $totalPrice = ($roomType->base_price * $request->rooms_count) * $days;

            // 6. Create Booking
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'bookable_id' => $request->hotel_id,
                'bookable_type' => Hotel::class,
                'room_type_id' => $roomType->id, // SAVE THE TIER ID
                'booking_code' => 'BNS-H-' . strtoupper(Str::random(8)),
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'guests_count' => $request->guests_count,
                'rooms_count' => $request->rooms_count,
                'total_price' => $totalPrice,
                'status' => 'confirmed',
            ]);

            return $this->successResponse($booking, 'Booking confirmed!');
        });
    }
}
