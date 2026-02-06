<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VendorStatusController extends Controller
{
    //
     public function check(Request $request)
    {
        $user = $request->user();
        // Business Logic: Ensure they are actually a vendor
        if ($user->role !== 'vendor') {
            return response()->json(['message' => 'Not a vendor account'], 403);
        }

        return response()->json([
            'status' => $user->status, // Returns 'pending', 'active', or 'suspended'
            'message' => $this->getStatusMessage($user->status),
        ]);
    }

      private function getStatusMessage($status)
    {
        return match ($status) {
            'active' => 'Your account is approved and ready to use.',
            'pending' => 'Your account is currently under review by our admin team.',
            'suspended' => 'Your account has been suspended. Please contact support.',
            default => 'Unknown status.',
        };
    }
}
