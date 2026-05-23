<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    // ================= LIST =================
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Event::query()
            ->where('barangay', $user->barangay)
            ->orderBy('event_date', 'asc');

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        return response()->json($query->paginate(10));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string',
            'incident_id' => 'nullable|integer',
        ]);

        $validated['barangay'] = $user->barangay;
        $validated['created_by'] = $user->id;

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'data' => $event
        ], 201);
    }

    // ================= SHOW =================
    public function show($id)
    {
        $user = auth()->user();

        $event = Event::where('barangay', $user->barangay)
            ->findOrFail($id);

        return response()->json($event);
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $event = Event::where('barangay', $user->barangay)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string',
            'incident_id' => 'nullable|integer',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'data' => $event
        ]);
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $user = auth()->user();

        $event = Event::where('barangay', $user->barangay)
            ->findOrFail($id);

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
