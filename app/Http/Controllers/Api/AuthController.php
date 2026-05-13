<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * Register a new user and create a token.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:users,phone',
            'password' => 'required|min:8',
            'device' => 'nullable|in:web,mobile',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
        ]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User registration failed'
            ], 500);
        }

        // 🌐 WEB (cookie session)
        if ($request->device === 'web') {
            auth()->login($user);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Web registration successful',
                'user' => $user,
                'type' => 'cookie'
            ], 201);
        }

        // 📱 MOBILE (token auth)
        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Mobile registration successful',
            'user' => $user,
            'token' => $token,
            'type' => 'token'
        ], 201);
    }

    /**
     * Login a new user and create a token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
            'device' => 'nullable|in:web,mobile',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => [
                    'phone' => ['No account found with this phone number']
                ]
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => [
                    'password' => ['Incorrect password']
                ]
            ], 401);
        }

        // 🌐 WEB LOGIN (cookie session)
        if ($request->device === 'web') {
            auth()->login($user);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Web login successful',
                'user' => $user,
                'type' => 'cookie'
            ]);
        }

        // 📱 MOBILE LOGIN (token)
        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Mobile login successful',
            'user' => $user,
            'token' => $token,
            'type' => 'token'
        ]);
    }
}
