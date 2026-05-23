<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvacuationResident;
use Illuminate\Http\Request;

class EvacuationResidentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = EvacuationResident::where('barangay', $user->barangay);

        if ($request->event_id) {
            $query->where('evacuation_event_id', $request->event_id);
        }

        return $query->latest()->get();
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'evacuation_event_id' => 'required',
            'evacuation_center_id' => 'required',
            'resident_name' => 'required|string',
            'contact_number' => 'nullable|string',
            'family_members' => 'nullable|integer',
        ]);

        $validated['barangay'] = $user->barangay;
        $validated['status'] = 'inside';

        return EvacuationResident::create($validated);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $resident = EvacuationResident::where('barangay', $user->barangay)
            ->findOrFail($id);

        $resident->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Updated',
            'data' => $resident
        ]);
    }

    public function destroy($id)
    {
        $user = auth()->user();

        EvacuationResident::where('barangay', $user->barangay)
            ->findOrFail($id)
            ->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
