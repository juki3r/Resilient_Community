<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;


class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = User::find(auth()->id());

        $query = Incident::where('barangay', $user->barangay);

        if ($request->search) {

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

        $incidents = $query
            ->latest()
            ->paginate(10);

        return response()->json($incidents);
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
        $incident = Incident::findOrFail($id);

        $request->validate([
            'incident_type' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'reported_by' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable',
            'status' => 'required|string',
            'action_taken' => 'nullable|string',
        ]);

        $incident->update([
            'incident_type' => $request->incident_type,
            'category' => $request->category,
            'description' => $request->description,
            'location' => $request->location,
            'reported_by' => $request->reported_by,
            'contact_number' => $request->contact_number,
            'incident_date' => $request->incident_date,
            'incident_time' => $request->incident_time,
            'status' => $request->status,
            'action_taken' => $request->action_taken,
        ]);

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
