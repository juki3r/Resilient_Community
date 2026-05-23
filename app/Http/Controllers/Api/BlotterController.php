<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class BlotterController extends Controller
{
    // =========================
    // GET ALL BLOTTERS
    // =========================
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Blotter::with(['complainant', 'respondent'])
            ->latest();

        // SEARCH SUPPORT (IMPORTANT)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('incident_type', 'like', "%$search%")
                    ->orWhere('incident_location', 'like', "%$search%")
                    ->orWhere('incident_details', 'like', "%$search%")
                    ->orWhere('complainant_name', 'like', "%$search%");
            });
        }

        // PAGINATION (MATCH FRONTEND)
        $blotters = $query->paginate(10);

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

        DB::transaction(function () use (&$validated, &$blotter) {

            $year = date('Y');

            // GET LAST NUMBER FOR THIS YEAR
            $last = Blotter::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;

            if ($last && $last->blotter_number) {
                $parts = explode('-', $last->blotter_number);
                $nextNumber = intval(end($parts)) + 1;
            }

            $validated['blotter_number'] =
                'BLT-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $validated['status'] = $validated['status'] ?? 'Pending';
            $validated['priority_level'] = $validated['priority_level'] ?? 'Medium';

            $blotter = Blotter::create($validated);
        });

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
