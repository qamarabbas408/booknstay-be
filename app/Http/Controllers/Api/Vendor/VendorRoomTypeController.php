<?php
namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Http\Resources\Vendor\RoomTypeResource;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorRoomTypeController extends Controller
{
    use ApiResponser;

    // 1. List all rooms for a specific hotel
    public function index(Hotel $hotel)
    {
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $this->successResponse(
            RoomTypeResource::collection($hotel->roomTypes),
            'Room tiers retrieved'
        );
    }

    // 2. Create a new room tier
    public function store(Request $request, Hotel $hotel)
    {
        // 1. Security Check
        if ($hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Validation
        // The '*.' syntax tells Laravel to validate every item in the root-level array
        $request->validate([
            '*.name' => 'required|string|max:100',
            '*.base_price' => 'required|numeric|min:0',
            '*.max_occupancy' => 'required|integer|min:1',
            '*.total_inventory' => 'required|integer|min:1',
            '*.description' => 'nullable|string',
        ]);

        // 3. Database Transaction
        return DB::transaction(function () use ($request, $hotel) {
            $createdTiers = [];

            // 4. Loop through the array of rooms sent from React
            foreach ($request->all() as $roomData) {

                // Optional: Check if this name already exists for this hotel to avoid duplicates
                $exists = $hotel->roomTypes()->where('name', $roomData['name'])->exists();

                if (!$exists) {
                    $createdTiers[] = $hotel->roomTypes()->create([
                        'name' => $roomData['name'],
                        'base_price' => $roomData['base_price'],
                        'max_occupancy' => $roomData['max_occupancy'],
                        'total_inventory' => $roomData['total_inventory'],
                        'description' => $roomData['description'] ?? null,
                    ]);
                }
            }

            return $this->successResponse(
                $createdTiers,
                count($createdTiers) . ' room tiers added successfully',
                201
            );
        });
    }

    // 3. Update a room tier
    public function update(Request $request, RoomType $roomType)
    {
        // Ensure vendor owns the hotel this room belongs to
        if ($roomType->hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Business Logic Guard: Don't allow price/occupancy changes if bookings exist
        if ($roomType->bookings()->whereIn('status', ['confirmed', 'completed'])->exists()) {
            return response()->json([
                'message' => 'Cannot modify room details while active bookings exist.'
            ], 422);
        }

        $roomType->update($request->all());

        return $this->successResponse(new RoomTypeResource($roomType), 'Room tier updated');
    }

    // 4. Delete a room tier
    public function destroy(RoomType $roomType)
    {
        if ($roomType->hotel->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($roomType->bookings()->exists()) {
            return response()->json(['message' => 'Cannot delete room with booking history'], 422);
        }

        $roomType->delete();
        return $this->successResponse(null, 'Room tier deleted');
    }
}
