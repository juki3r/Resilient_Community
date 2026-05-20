<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * REGISTER USER (TOKEN ONLY)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'phone' => 'required|unique:users,phone',

            'province' => 'required',
            'municipality' => 'required',

            'username' => 'required|unique:users,username',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'fullname' => ucwords(strtolower($request->fullname)),
            'phone' => $request->phone,

            'province' => ucwords(strtolower($request->province)),
            'municipality' => ucwords(strtolower($request->municipality)),

            'username' => strtolower(trim($request->username)),
            'role' => 'resident',
            'phone_verified' => false,
            'granted' => false,

            'password' => bcrypt($request->password),
        ]);


        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * LOGIN USER (TOKEN ONLY)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Normalize username (Admin, ADMIN, admin → admin)
        $username = strtolower(trim($request->username));

        // Find user
        $user = User::whereRaw('LOWER(username) = ?', [$username])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => [
                    'username' => ['Invalid credentials.'],
                ],
            ], 404);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => [
                    'password' => ['Invalid credentials.'],
                ],
            ], 401);
        }

        // Optional: account approval check
        if ($user->phone_verified && !$user->granted) {
            return response()->json([
                'success' => false,
                'status'  => 'approval_needed',
                'message' => 'Your account is pending approval.',
                'user' => $user,
            ], 403);
        }

        // Phone verification / OTP flow
        if (!$user->phone_verified) {

            // Reuse existing OTP if sent within the last 60 seconds
            if (
                $user->otp_sent_at &&
                Carbon::parse($user->otp_sent_at)->diffInSeconds(now()) < 60
            ) {
                $otp = $user->otp_code;
            } else {
                $otp = rand(100000, 999999);

                $user->update([
                    'otp_code'       => $otp,
                    'otp_expires_at' => now()->addMinutes(5),
                    'otp_sent_at'    => now(),
                ]);

                // Send SMS
                Http::withHeaders([
                    'X-API-KEY' => 'qHafeGIG2dWbb5QEKdW1jR2J0rhNbIr0wjeyfkeY',
                ])->post('https://carlesppo.com/api/send-sms-api', [
                    'phone_number' => $user->phone,
                    'message'      => "Your OTP is: {$otp}",
                ]);
            }

            return response()->json([
                'success' => false,
                'status'  => 'otp_required',
                'message' => 'OTP sent to your phone.',
                'phone'   => $user->phone,
            ], 403);
        }

        // Delete old tokens (optional)
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token'   => $token,
            'user'    => [
                'id'              => $user->id,
                'fullname'        => $user->fullname,
                'username'        => $user->username,
                'phone'           => $user->phone,
                'province'        => $user->province,
                'municipality'    => $user->municipality,
                'role'            => $user->role,
                'granted'         => (bool) $user->granted,
                'phone_verified'  => (bool) $user->phone_verified,
            ],
        ], 200);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required|digits:6'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // expired check (5 minutes)
        if (!$user->otp_sent_at || Carbon::parse($user->otp_sent_at)->addMinutes(5)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        if ($user->otp_code !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 401);
        }

        // ✅ mark verified
        $user->update([
            'phone_verified' => true,
            'otp_code' => null,
            'otp_sent_at' => null,
        ]);




        // Optional: account approval check
        if (!$user->granted) {
            return response()->json([
                'success' => false,
                'status'  => 'approval_needed',
                'message' => 'Your account is pending approval.',
                'user' => $user
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;



        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
            'otp_sent_at' => now(),
        ]);

        // send SMS
        Http::withHeaders([
            'X-API-KEY' => "qHafeGIG2dWbb5QEKdW1jR2J0rhNbIr0wjeyfkeY",
        ])->post('https://carlesppo.com/api/send-sms-api', [
            'phone_number' => $user->phone,
            'message' => "Your OTP is: $otp"
        ]);


        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully'
        ]);
    }

    /**
     * LOGOUT (REVOKE TOKEN)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
