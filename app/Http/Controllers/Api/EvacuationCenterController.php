<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvacuationCenter;
use Illuminate\Http\Request;

class EvacuationCenterController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return EvacuationCenter::where('barangay', $user->barangay)
            ->orderBy('status', 'asc')
            ->latest()
            ->paginate(10);
    }
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'capacity' => 'nullable|integer',
            'event_type' => 'required|string',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
        ]);

        $validated['barangay'] = $user->barangay;
        $validated['created_by'] = $user->id;
        $validated['status'] = 'active';

        return EvacuationCenter::create($validated);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $center = EvacuationCenter::where('barangay', $user->barangay)
            ->findOrFail($id);

        $validated = $request->validate([
            'end_date' => 'nullable|date',
            'end_time' => 'nullable',
            'status' => 'nullable|in:active,ended',
        ]);

        $center->update($validated);

        return response()->json([
            'message' => 'Evacuation updated',
            'data' => $center
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        EvacuationCenter::where('barangay', $user->barangay)
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
