<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Incident;
use App\Models\MobileUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Incident::query();

        // Scope by role
        if ($user->role === "bdrrmo_admin") {
            $query->where('barangay', $user->barangay);
        } else {
            $query->where('municipality', $user->municipality);
        }

        // Search filter (shared logic)
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('incident_no', 'like', "%{$search}%")
                    ->orWhere('incident_type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('reported_by', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);


        $year = date('Y');

        $latest = Incident::latest()->first();

        $number = 1;

        if ($latest) {
            $lastId = $latest->id + 1;
            $number = str_pad($lastId, 4, '0', STR_PAD_LEFT);
        } else {
            $number = "0001";
        }

        $incidentNo = "INC-{$year}-{$number}";

        $incident = Incident::create([
            'barangay' => $user->barangay,
            'user_id' => $user->id,
            'incident_no' => $incidentNo,
            'type' => $request->type,
            'description' => $request->description,
            'location' => $request->location,
            'reported_by' => $user->fullname,
            'contact_number' => $user->phone,
            'municipality' => $user->municipality,
            'province' => $user->province,
            'incident_datetime' => now(),
        ]);

        //Send to MDRRMO
        // SendAdminNotificationJob::dispatch(
        //     'mdrrmo',
        //     [
        //         'title' => 'Incident Report',
        //         'body' => "New incident report from {$user->full_name}",
        //         'sms' => "[AlertoPH ALERT]\n{$user->full_name} submitted an incident report!",
        //         'request_id' => $user->id,
        //         'url' => '/incidents'
        //     ],
        //     $user->municipality
        // );

        //Send to BDRRMO
        // SendAdminNotificationJob::dispatch(
        //     'admin',
        //     [
        //         'title' => 'Incident Report',
        //         'body' => "New incident report from {$user->full_name}",
        //         'sms' => "[AlertoPH ALERT]\n{$user->full_name} submitted an incident report!",
        //         'request_id' => $user->id,
        //         'url' => '/incidents'
        //     ],
        //     $user->barangay
        // );


        //Send to user reminding the he/she sent an incident report
        // $title = "Incident Report Submitted!";
        // $body  = "Thank you for submitting incident report! \n We will give you an update on this.";

        // (new \App\Services\FirebaseService)->sendNotification(
        //     $user->fcm_token,
        //     $title,
        //     $body,
        //     [
        //         'screen' => 'Home',
        //         'request_id' => (string) $request->id,
        //     ]
        // );

        // //  SEND SMS
        // try {
        //     Http::withHeaders([
        //         'X-API-KEY' => env('SMS_API_KEY')
        //     ])->post('https://carlesppo.com/api/send-sms-api', [
        //         'phone_number' => $user->phone,
        //         'message' => "[AlertoPH]\n$title\n$body"
        //     ]);
        // } catch (\Exception $e) {
        //     \Log::error('SMS failed: ' . $e->getMessage());
        // }

        return response()->json([
            'message' => 'Incident created successfully',
            'incident' => $incident,
        ], 201);
    }

    public function store_appuser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);


        $year = date('Y');

        $latest = Incident::latest()->first();

        $number = 1;

        if ($latest) {
            $lastId = $latest->id + 1;
            $number = str_pad($lastId, 4, '0', STR_PAD_LEFT);
        } else {
            $number = "0001";
        }

        $incidentNo = "INC-{$year}-{$number}";

        $incident = Incident::create([
            'barangay' => $user->barangay,
            'mobileuser_id' => $user->id,
            'incident_no' => $incidentNo,
            'type' => $request->type,
            'description' => $request->description,
            'location' => $request->location,
            'reported_by' => $user->full_name,
            'contact_number' => $user->phone,
            'municipality' => $user->municipality,
            'province' => $user->province,
            'incident_datetime' => now(),
        ]);

        //Send to MDRRMO
        SendAdminNotificationJob::dispatch(
            'mdrrmo',
            [
                'title' => 'Incident Report',
                'body' => "New incident report from {$user->full_name}",
                'sms' => "[AlertoPH ALERT]\n{$user->full_name} submitted an incident report!",
                'request_id' => $user->id,
                'url' => '/incidents'
            ],
            $user->municipality
        );

        //Send to BDRRMO
        SendAdminNotificationJob::dispatch(
            'admin',
            [
                'title' => 'Incident Report',
                'body' => "New incident report from {$user->full_name}",
                'sms' => "[AlertoPH ALERT]\n{$user->full_name} submitted an incident report!",
                'request_id' => $user->id,
                'url' => '/incidents'
            ],
            $user->barangay
        );


        //Send to user reminding the he/she sent an incident report
        $title = "Incident Report Submitted!";
        $body  = "Thank you for submitting incident report! \n We will give you an update on this.";

        (new \App\Services\FirebaseService)->sendNotification(
            $user->fcm_token,
            $title,
            $body,
            [
                'screen' => 'Home',
                'request_id' => (string) $request->id,
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
            'message' => 'Incident created successfully',
            'incident' => $incident,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $incident = Incident::findOrFail($id);

        return response()->json($incident);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $request->validate([
            'status' => 'required|in:received,action_taken,resolved,declined',
        ]);

        $incident = Incident::findOrFail($id);


        $incident->update([
            'status' => $request->status,
        ]);

        //We will notify the resident submits the alert incident
        // ✅ FIXED USER LOOKUP
        $mobileuser = MobileUser::find($incident->mobileuser_id);

        if (!$mobileuser) {
            return response()->json(['message' => 'User not found']);
        }

        $title = "Incident report Update";
        $body  = "Your incident report has been " . $request->status;

        // ================= FCM =================
        if ($mobileuser->fcm_token) {
            (new \App\Services\FirebaseService)->sendNotification(
                $mobileuser->fcm_token,
                $title,
                $body,
                [
                    'screen' => 'Home',
                    'requests_id' => (string) $incident->id,
                ]
            );
        }

        // ================= SMS =================
        try {
            if ($mobileuser->phone) {
                Http::withHeaders([
                    'X-API-KEY' => env('SMS_API_KEY')
                ])->post('https://carlesppo.com/api/send-sms-api', [
                    'phone_number' => $mobileuser->phone,
                    'message' => "[AlertoPH ALERT]\n$title\n$body"
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('SMS failed: ' . $e->getMessage());
        }


        return response()->json([
            'message' => 'Incident updated successfully',
            'incident' => $incident,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $incident = Incident::findOrFail($id);

        $incident->delete();

        return response()->json([
            'message' => 'Incident deleted successfully',
        ]);
    }
}
