<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlotterController extends Controller
{
    // =========================
    // GET ALL BLOTTERS
    // =========================
    public function index()
    {
        $blotters = Blotter::with(['complainant', 'respondent'])
            ->latest()
            ->get();

        return response()->json($blotters);
    }

    // =========================
    // STORE NEW BLOTTER
    // =========================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'incident_type' => 'required|string',
            'incident_category' => 'nullable|string',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable',
            'incident_location' => 'required|string',
            'incident_details' => 'required|string',

            'complainant_id' => 'nullable|exists:residents,id',
            'complainant_name' => 'required|string',

            'respondent_id' => 'nullable|exists:residents,id',
            'respondent_name' => 'nullable|string',

            'status' => 'nullable|string',
            'priority_level' => 'nullable|string',
        ]);

        $validated['blotter_number'] = 'BLT-' . strtoupper(Str::random(8));
        $validated['status'] = $validated['status'] ?? 'Pending';
        $validated['priority_level'] = $validated['priority_level'] ?? 'Medium';

        $blotter = Blotter::create($validated);

        return response()->json([
            'message' => 'Blotter created successfully',
            'data' => $blotter
        ], 201);
    }

    // =========================
    // SHOW SINGLE BLOTTER
    // =========================
    public function show($id)
    {
        $blotter = Blotter::with(['complainant', 'respondent'])
            ->findOrFail($id);

        return response()->json($blotter);
    }

    // =========================
    // UPDATE BLOTTER
    // =========================
    public function update(Request $request, $id)
    {
        $blotter = Blotter::findOrFail($id);

        $validated = $request->validate([
            'incident_type' => 'sometimes|string',
            'incident_category' => 'nullable|string',
            'incident_date' => 'sometimes|date',
            'incident_time' => 'nullable',
            'incident_location' => 'sometimes|string',
            'incident_details' => 'sometimes|string',

            'status' => 'nullable|string',
            'priority_level' => 'nullable|string',

            'action_taken' => 'nullable|string',
            'resolution' => 'nullable|string',
            'settlement_date' => 'nullable|date',
        ]);

        $blotter->update($validated);

        return response()->json([
            'message' => 'Blotter updated successfully',
            'data' => $blotter
        ]);
    }

    // =========================
    // SOFT DELETE
    // =========================
    public function destroy($id)
    {
        $blotter = Blotter::findOrFail($id);
        $blotter->delete();

        return response()->json([
            'message' => 'Blotter deleted successfully'
        ]);
    }

    // =========================
    // RESTORE DELETED BLOTTER
    // =========================
    public function restore($id)
    {
        $blotter = Blotter::withTrashed()->findOrFail($id);
        $blotter->restore();

        return response()->json([
            'message' => 'Blotter restored successfully'
        ]);
    }

    // =========================
    // FORCE DELETE (PERMANENT)
    // =========================
    public function forceDelete($id)
    {
        $blotter = Blotter::withTrashed()->findOrFail($id);
        $blotter->forceDelete();

        return response()->json([
            'message' => 'Blotter permanently deleted'
        ]);
    }

    // =========================
    // GET ONLY DELETED
    // =========================
    public function trashed()
    {
        $blotters = Blotter::onlyTrashed()->get();

        return response()->json($blotters);
    }
}
