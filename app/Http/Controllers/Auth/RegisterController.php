<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validate the incoming data from React
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:guest,vendor', // Don't let them register as
            'interests' => 'nullable|array', // Array of Interest IDs: [1, 4, 7]

        ]);

        // dd($request->role);
        // 2. Create the User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => ($request->role === 'vendor') ? 'pending' : 'active',
        ]);

        if ($request->has('interests')) {
            // We use sync() to attach the IDs to the pivot table
            $user->interests()->sync($request->interests);
        }

        // 3. Create a token so they are "logged in" immediately
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Return JSON to React
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function registerVendor(Request $request)
    {
        // 1. Validation (Matches your React FormData interface)
        $request->validate([
            'ownerName' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'businessName' => 'required|string',
            'businessType' => 'required|in:hotel,resort,event_venue,other',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'zipCode' => 'required|string',
            'description' => 'required|string|min:50',
            'capacity' => 'required|integer',
            'priceRange' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048', // Each image max 2MB
        ]);

        // 2. Start Transaction
        return DB::transaction(function () use ($request) {

            // A. Create the User (The Login Account)
            $user = User::create([
                'name' => $request->ownerName,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'vendor',
                'status' => 'pending', // Vendor needs manual approval
            ]);

            // B. Create Vendor Profile (The Business Identity)
            $user->vendorProfile()->create([
                'business_name' => $request->businessName,
                'business_type' => $request->businessType,
                'business_email' => $request->email,
                'business_phone' => $request->phone,
                'website' => $request->website,
            ]);

            // C. Create the First Property (The Hotel)
            $hotel = $user->hotels()->create([
                'name' => $request->businessName,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'zip_code' => $request->zipCode,
                'total_rooms' => $request->rooms,
                'max_capacity' => $request->capacity,
                'price_range' => $request->priceRange,
                'status' => 'pending',
            ]);

            // D. Handle Image Uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    // Store the file in 'public/hotels' folder
                    $path = $image->store('hotels', 'public');

                    // Save record in database
                    $hotel->images()->create([
                        'image_path' => $path,
                        'is_primary' => ($index === 0), // Set first image as primary
                    ]);
                }
            }

            return response()->json([
                'message' => 'Vendor registration successful. Your account is under review.',
                // 'user' => $user->load('vendorProfile'),
                // 'hotel' => $hotel->load('images')
                'hotel' => $hotel->load('images'),

            ], 201);
        });
    }
}
