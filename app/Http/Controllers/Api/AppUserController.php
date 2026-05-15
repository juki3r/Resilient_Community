<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use App\Services\FirebaseService;

class AppUserController extends Controller
{
    /**
     * REGISTER USER (TOKEN ONLY)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'required|unique:mobile_users,phone',
            'barangay' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $barangay_belongs = User::where('barangay', $request->barangay)->value('id');

        if ($barangay_belongs) {
            $mobileuser = MobileUser::create([
                'user_id' => $barangay_belongs,
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'role' => 'resident',
                'phone_verified' => false,
                'granted' => false,
                'barangay' => $request->barangay,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mobile User registered successfully',
                'user' => $mobileuser,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User registered failed',
            ], 201);
        }
    }

    /**
     * LOGIN USER (TOKEN ONLY)
     */
    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required',
    //         'password' => 'required',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $user = User::where('phone', $request->phone)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User not found',
    //             'errors' => [
    //                 'phone' => ['No account found with this phone number']
    //             ]
    //         ], 404);
    //     }

    //     if (!Hash::check($request->password, $user->password)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid credentials',
    //             'errors' => [
    //                 'password' => ['Incorrect password']
    //             ]
    //         ], 401);
    //     }

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Login successful',
    //         'user' => $user,
    //         'token' => $token,
    //     ], 200);
    // }

    // /**
    //  * LOGOUT (REVOKE TOKEN)
    //  */
    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Logged out successfully'
    //     ]);
    // }



















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

            // 1️⃣ SAVE NOTIFICATION (USER-BASED)
            // Notification::create([
            //     'user_id' => $user->id,
            //     'title'   => $title,
            //     'body' => $body,
            //     'type'    => 'admin_message',
            //     'is_read' => false,
            // ]);

            // FIREBASE PUSH
            (new \App\Services\FirebaseService)->sendNotification(
                $mobileuser->fcm_token,
                $title,
                \Illuminate\Support\Str::limit($body, 160),
                [
                    'screen' => 'Requests',
                    'requests_id' => (string) $mobileuser->id,
                ]
            );

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
                'data' => $title
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
