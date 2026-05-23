<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvacuationEvent;
use Illuminate\Http\Request;

class EvacuationEventController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return EvacuationEvent::where('barangay', $user->barangay)
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
        ]);

        $validated['barangay'] = $user->barangay;
        $validated['created_by'] = $user->id;

        return EvacuationEvent::create($validated);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $event = EvacuationEvent::where('barangay', $user->barangay)
            ->findOrFail($id);

        $event->update($request->all());

        return response()->json([
            'message' => 'Updated',
            'data' => $event
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        EvacuationEvent::where('barangay', $user->barangay)
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
