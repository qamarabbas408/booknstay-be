<?php

namespace App\Http\Controllers\Auth\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class RegistrationController extends Controller
{
    public function registerVendor(Request $request)
    {
        $request->validate([
            'ownerName' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'companyName' => 'required|string',
            'businessType' => 'required|in:hotel,resort,event_venue,other',
            'phone' => 'required|string',
            'website' => 'required|string|url',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->ownerName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'vendor',
                'status' => 'pending', // Still needs admin approval to go "Live"
            ]);

            $user->vendorProfile()->create([
                'business_name' => $request->companyName,
                'business_type' => $request->businessType,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user->load('vendorProfile'),
                'access_token' => $token,
            ], 201);
        });
    }

    public function checkApprovalStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

    }
}
