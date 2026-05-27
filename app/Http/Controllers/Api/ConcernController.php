<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Concern;
use App\Models\MobileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConcernController extends Controller
{
    // =========================
    // GET ALL BLOTTERS
    // =========================
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $query = Concern::with('full_name')->where('barangay', $user->barangay)->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('location', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        return $query->paginate(10);
    }

    public function index_appuser(Request $request)
    {

        return response()->json([
            'concerns' => Concern::where('user_id', auth()->user()->id)
                ->where('barangay', auth()->user()->barangay)
                ->latest()
                ->get()
        ]);
    }


    public function store_appuser(Request $request)
    {

        $request->validate([
            'title' => 'required',
            'location' => 'required',
            'description' => 'required',
        ]);

        $user = auth()->user();

        $concern = Concern::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'location' => $request->location,
            'description' => $request->description,
            'status' => 'submitted',
            'progress' => 0,
            'barangay' => $user->barangay,
        ]);


        // ========= This section will alert admin that user request certifications =======
        // ========= Use FCM admin app, Sms to notify admin ===============================


        // ONLY THIS LINE (NO LOOPS, NO SMS, NO FIREBASE HERE)
        // NotifyAdminsJob::dispatch($documentRequest->id);

        SendAdminNotificationJob::dispatch(
            'admin',
            [
                'title' => 'New Concern',
                'body' => "New concern from {$user->full_name}",
                'sms' => "[AlertoPH ALERT]\n{$user->full_name} submitted a concern!",
                'request_id' => $user->id,
                'url' => '/concerns'
            ],
            $user->barangay
        );



        return response()->json([
            'message' => 'Concern created successfully',
            'data' => $concern
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {

        $request->validate([
            'status' => 'required|in:received,under_review,in_progress,resolved,rejected'
        ]);

        $concern = Concern::findOrFail($id);

        $concern->update([
            'status' => $request->status,
            'admin_read' => true
        ]);



        // Get user from request (IMPORTANT FIX)
        $user = MobileUser::find($concern->user_id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found for this concern'
            ]);
        }

        if (!$user->fcm_token) {
            return response()->json([
                'success' => false,
                'message' => 'FCM token not found for user'
            ]);
        }

        $title = "Concern Update !";
        $body  = "Your concern is " . $request->status;

        (new \App\Services\FirebaseService)->sendNotification(
            $user->fcm_token,
            $title,
            $body,
            [
                'screen' => 'Concerns',
                'concerns_id' => (string) $concern->id,
            ]
        );

        //  SEND SMS
        try {
            Http::withHeaders([
                'X-API-KEY' => env('SMS_API_KEY')
            ])->post('https://carlesppo.com/api/send-sms-api', [
                'phone_number' => $user->phone,
                'message' => "[AlertoPH]\n$title\n$body"
            ]);
        } catch (\Exception $e) {
            \Log::error('SMS failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'status' => $request->status,
            'message' => 'Concern status updated successfully'
        ]);
    }

    //Delete Concern
    public function destroy($id)
    {
        $request = Concern::findOrFail($id);
        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Concern deleted successfully'
        ]);
    }
}
