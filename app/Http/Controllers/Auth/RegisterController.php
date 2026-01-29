<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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
}
