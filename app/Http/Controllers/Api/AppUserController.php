<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileUser;
use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AppUserController extends Controller
{
    /**
     * REGISTER USER (TOKEN ONLY)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'phone' => 'required|unique:mobile_users,phone|min:11|max:11',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }


        $barangay_belongs = User::where('barangay', $request->barangay)->value('id');

        if (!$barangay_belongs) {
            return response()->json([
                'success' => false,
                'message' => 'Barangay not found'
            ], 404);
        }

        $mobileuser = MobileUser::create([
            'user_id' => $barangay_belongs,
            'full_name' => $request->full_name,
            'province' => $request->province,
            'municipality' => $request->municipality,
            'barangay' => ucwords(strtolower($request->barangay)),
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'resident',
            'phone_verified' => false,
            'granted' => false,

        ]);

        // ✅ OTP generate
        $otp = rand(100000, 999999);

        // ⚠️ FIX: update correct model
        $mobileuser->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
            'otp_sent_at' => now(),
        ]);

        // ✅ Send SMS
        Http::withHeaders([
            'X-API-KEY' => "qHafeGIG2dWbb5QEKdW1jR2J0rhNbIr0wjeyfkeY",
        ])->post('https://carlesppo.com/api/send-sms-api', [
            'phone_number' => $mobileuser->phone,
            'message' => "Your OTP is: $otp"
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'user_id' => $mobileuser->id, // IMPORTANT for React Native
            'phone' => $mobileuser->phone,
        ], 201);
    }

    /**
     * LOGIN USER (TOKEN ONLY)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $mobileuser = MobileUser::where('phone', $request->phone)->first();

        if (!$mobileuser) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile user not found',
                'errors' => [
                    'phone' => ['No account found with this phone number']
                ]
            ], 404);
        }

        if (!Hash::check($request->password, $mobileuser->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => [
                    'password' => ['Incorrect password']
                ]
            ], 401);
        }

        if ($mobileuser->is_logged_in) {
            return response()->json([
                'success' => false,
                'message' => 'Account already logged in!',
                'errors' => [
                    'phone' => ['Duplicate logged in is prohibited']
                ]
            ], 404);
        }



        if (!$mobileuser->phone_verified) {
            // check cooldown (avoid spam)
            if ($mobileuser->otp_sent_at && Carbon::parse($mobileuser->otp_sent_at)->diffInSeconds(now()) < 60) {
                $otp = $mobileuser->otp_code; // reuse existing OTP
                return response()->json([
                    'success' => true,
                    'message' => 'Please verify phone number!',
                    'user' => $mobileuser,
                ], 200);
            } else {
                $otp = rand(100000, 999999);

                $mobileuser->update([
                    'otp_code' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(5),
                    'otp_sent_at' => now(),
                ]);

                // send SMS
                Http::withHeaders([
                    'X-API-KEY' => "qHafeGIG2dWbb5QEKdW1jR2J0rhNbIr0wjeyfkeY",
                ])->post('https://carlesppo.com/api/send-sms-api', [
                    'phone_number' => $mobileuser->phone,
                    'message' => "Your OTP is: $otp"
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Please verify phone number!',
                    'user' => $mobileuser,
                ], 200);
            }
        }

        if ($mobileuser->granted) {
            $mobileuser->is_logged_in = true;
            $mobileuser->save();
        }

        $token = $mobileuser->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $mobileuser,
            'token' => $token,
        ], 200);
    }

    public function verifyOtp(Request $request)
    {

        $mobileuser = MobileUser::where('phone', $request->phone)->first();

        $request->validate([
            'phone' => ['required'],
            'otp' => ['required'],
        ]);

        $user = MobileUser::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->otp_code) {
            return response()->json([
                'message' => 'No OTP requested'
            ], 400);
        }

        if (now()->gt($user->otp_expires_at)) {
            return response()->json([
                'message' => 'OTP expired'
            ], 400);
        }

        if ($user->otp_code !== $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 401);
        }

        // ✅ mark verified
        $user->update([
            'phone_verified' => true,
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        if ($mobileuser->granted) {
            $mobileuser->is_logged_in = true;
            $mobileuser->save();
        }

        // login token after verification
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'OTP verified successfully',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /* ================================
        1. SEND FORGOT OTP
    ================================= */
    public function sendForgotOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $user = MobileUser::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Please register to continue'
            ], 404);
        }

        // generate 6 digit OTP
        $otp = rand(100000, 999999);

        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
        ]);

        // SEND SMS (your API)
        Http::withHeaders([
            'X-API-KEY' => "qHafeGIG2dWbb5QEKdW1jR2J0rhNbIr0wjeyfkeY"
        ])->post('https://carlesppo.com/api/send-sms-api', [
            'phone_number' => $user->phone,
            'message' => "Your reset OTP is: $otp"
        ]);

        return response()->json([
            'message' => 'OTP sent successfully'
        ]);
    }

    /* ================================
        2. VERIFY RESET OTP
    ================================= */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|string',
        ]);

        $user = MobileUser::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Convert both values to string to avoid int vs string mismatch
        if ((string) $user->otp_code !== (string) $request->otp) {
            return response()->json([
                'message' => 'Invalid OTP'
            ], 401);
        }

        // Handle null expiration safely
        if (!$user->otp_expires_at) {
            return response()->json([
                'message' => 'OTP expiration not set'
            ], 401);
        }

        // Ensure Carbon instance
        if (now()->gt(Carbon::parse($user->otp_expires_at))) {
            return response()->json([
                'message' => 'OTP expired'
            ], 401);
        }

        // Clear OTP after successful verification (optional but recommended)
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'OTP verified',
            'reset_token' => encrypt($user->id),
        ]);
    }

    /* ================================
        3. RESET PASSWORD
    ================================= */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'reset_token' => 'required',
            'password' => 'required|min:6'
        ]);

        $userId = decrypt($request->reset_token);

        $user = MobileUser::find($userId);

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Password reset successful'
        ]);
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'token' => 'required',
        ]);

        MobileUser::where('id', $request->user_id)
            ->update([
                'fcm_token' => $request->token
            ]);

        return response()->json([
            'message' => 'FCM token saved'
        ]);
    }

    /**
     * ================================
     * SEND OTP (for manual resend from mobile app)
     * Endpoint: POST /api/send-otp
     * Body: { "phone": "09XXXXXXXXX" }
     * ================================
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/'
            ],
        ], [
            'phone.regex' => 'Phone number must start with 09 and be 11 digits.',
        ]);

        // Find user
        $user = MobileUser::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Phone number not registered'
            ], 404);
        }

        // Prevent spam: reuse existing OTP if sent within last 60 seconds
        if (
            $user->otp_sent_at &&
            Carbon::parse($user->otp_sent_at)->diffInSeconds(now()) < 60
        ) {
            return response()->json([
                'message' => 'Please wait before requesting another OTP'
            ], 429);
        }

        // Generate new 6-digit OTP
        $otp = rand(100000, 999999);

        // Save OTP
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(5),
            'otp_sent_at' => now(),
        ]);

        // Send SMS
        $response = Http::withHeaders([
            'X-API-KEY' => 'qHafeGIG2dWbb5QEKdW1jR2J0rhNbIr0wjeyfkeY',
        ])->post('https://carlesppo.com/api/send-sms-api', [
            'phone_number' => $user->phone,
            'message' => "Your OTP is: {$otp}",
        ]);

        // Optional: Check if SMS API failed
        if (!$response->successful()) {
            return response()->json([
                'message' => 'Failed to send OTP'
            ], 500);
        }

        return response()->json([
            'message' => 'OTP sent successfully'
        ]);
    }


    /**
     * LOGOUT (REVOKE TOKEN)
     */
    public function logout(Request $request)
    {
        $user = $request->user(); // should resolve MobileUser via guard

        if ($user) {
            $user->is_logged_in = false;
            $user->save();

            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }


















    public function index(Request $request)
    {
        $search = $request->search;

        $query = MobileUser::query()
            ->where('user_id', auth()->id());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return response()->json($users);
    }

    public function show(MobileUser $mobileUser)
    {
        return response()->json($mobileUser);
    }

    public function update(Request $request, MobileUser $mobileUser)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'purok' => 'nullable|string|max:255',
            'granted' => 'nullable|boolean',
            'email' => [
                'nullable',
                'email',
                Rule::unique('mobile_users', 'email')->ignore($mobileUser->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mobile_users', 'phone')->ignore($mobileUser->id),
            ],
            'phone_verified' => 'nullable|boolean',
            'fcm_token' => 'nullable|string',
            'password' => 'nullable|string|min:6|confirmed',
            'otp_code' => 'nullable|string|max:10',
            'otp_expires_at' => 'nullable|date',
            'otp_sent_at' => 'nullable|date',
            'role' => 'nullable|string|max:50',
            'barangay' => 'nullable|string|max:255',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $mobileUser->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $mobileUser->fresh(),
        ]);
    }

    public function destroy(MobileUser $mobileUser)
    {
        $mobileUser->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }



    public function sendToOne($id)
    {
        try {
            $mobileuser = MobileUser::findOrFail($id);

            if (!$mobileuser->fcm_token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User has no FCM token registered.'
                ], 400);
            }

            $title = request('title');
            $body  = request('body');

            // FIREBASE PUSH
            $fcmResponse = (new \App\Services\FirebaseService)->sendNotification(
                $mobileuser->fcm_token,
                $title,
                $body,
                [
                    'screen' => 'Requests',
                    'requests_id' => (string) $mobileuser->id,
                ]
            );
            \Log::info('FCM RESPONSE:', (array) $fcmResponse);

            // 3️⃣ SMS
            try {
                Http::withHeaders([
                    'X-API-KEY' => env('SMS_API_KEY')
                ])->post('https://carlesppo.com/api/send-sms-api', [
                    'phone_number' => $mobileuser->phone,
                    'message' => "[Daan Banwa ALERT]\n$title\n$body"
                ]);
            } catch (\Exception $e) {
                \Log::error('SMS failed: ' . $e->getMessage());
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Notification + SMS sent successfully.",
                'data' => $title,
                'fcm' => $mobileuser->fcm_token,
                'fcm_response' => $fcmResponse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
