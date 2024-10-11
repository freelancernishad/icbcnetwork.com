<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth; // Import JWTAuth
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // Create the user and hash the password
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password), // Hash the password
        ]);

        // Automatically log the user in after registration and generate a JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token, // Return the token
            'user' => $user
        ]);
    }

    // Login User
    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        // Attempt to verify the credentials and create a token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // On success, return the token and user info
        return response()->json([
            'message' => 'Login successful',
            'token' => $token, // Return the token
            'user' => Auth::user(), // Get authenticated user
        ]);
    }

    // Optional: Logout User (invalidate the token)
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }
    }
}
