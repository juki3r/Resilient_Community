<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\User;


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
            'incident_datetime' => now(),
        ]);

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
