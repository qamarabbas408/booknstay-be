<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        // 1. Validate Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Find the user
        $user = User::where('email', $request->email)->first();

        // 3. Check credentials and account status
        // We check if password is correct AND if the user is 'active'
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The credentials provided are incorrect.',
            ], 401);
        }

        // 1. If the user is BANNED or SUSPENDED, block them completely (Hard Block)
        if ($user->status === 'suspended') {
            return response()->json([
                'message' => 'Your account has been suspended. Please contact support.',
            ], 403);
        }
        // 4. Delete old tokens (Optional: prevents a single user from having 100s of active tokens)
        $user->tokens()->delete();

        // 5. Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }
}
