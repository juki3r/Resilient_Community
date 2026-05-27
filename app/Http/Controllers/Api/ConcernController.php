<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Concern;
use Illuminate\Http\Request;

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

        $query = Concern::where('barangay', $user->barangay)->latest();

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
}
