<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvacuationCenter;
use Illuminate\Http\Request;

class EvacuationCenterController extends Controller
{
    // ================= LIST =================
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = EvacuationCenter::where('barangay', $user->barangay)
            ->orderBy('created_at', 'desc');

        // SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('location', 'like', "%{$request->search}%")
                    ->orWhere('contact_person', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->paginate(10));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'nullable|integer',

            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',

            // evacuation timing
            'start_date' => 'nullable|date',
            'start_time' => 'nullable',
            'end_date' => 'nullable|date',
            'end_time' => 'nullable',

            'description' => 'nullable|string',

            'status' => 'nullable|in:inactive,active,ongoing,closed',
        ]);

        // AUTO ASSIGN BARANGAY
        $validated['barangay'] = $user->barangay;

        // DEFAULT STATUS LOGIC
        if (!isset($validated['status'])) {
            $validated['status'] = $validated['start_date'] ? 'ongoing' : 'inactive';
        }

        $center = EvacuationCenter::create($validated);

        return response()->json([
            'message' => 'Evacuation center saved successfully',
            'data' => $center
        ], 201);
    }

    // ================= SHOW =================
    public function show($id)
    {
        $user = auth()->user();

        $center = EvacuationCenter::where('barangay', $user->barangay)
            ->findOrFail($id);

        return response()->json($center);
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $center = EvacuationCenter::where('barangay', $user->barangay)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'nullable|integer',

            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',

            'start_date' => 'nullable|date',
            'start_time' => 'nullable',
            'end_date' => 'nullable|date',
            'end_time' => 'nullable',

            'description' => 'nullable|string',

            'status' => 'nullable|in:inactive,active,ongoing,closed',
        ]);

        // AUTO STATUS LOGIC (SMART)
        if (!empty($validated['start_date']) && empty($validated['end_date'])) {
            $validated['status'] = 'ongoing';
        }

        if (!empty($validated['end_date'])) {
            $validated['status'] = 'closed';
        }

        $center->update($validated);

        return response()->json([
            'message' => 'Evacuation center updated successfully',
            'data' => $center
        ]);
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $user = auth()->user();

        $center = EvacuationCenter::where('barangay', $user->barangay)
            ->findOrFail($id);

        $center->delete();

        return response()->json([
            'message' => 'Evacuation center deleted successfully'
        ]);
    }
}
