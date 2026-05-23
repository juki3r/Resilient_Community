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
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'nullable|string',
            'capacity' => 'nullable|integer',
            'contact_person' => 'nullable|string',
            'contact_number' => 'nullable|string',
        ]);

        $validated['barangay'] = $user->barangay;

        return EvacuationCenter::create($validated);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $center = EvacuationCenter::where('barangay', $user->barangay)
            ->findOrFail($id);

        $center->update($request->all());

        return response()->json([
            'message' => 'Updated',
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
