<?php
// app/Http/Controllers/Api/OrdinanceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ordinance;

class OrdinanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Ordinance::where('user_id', $user->id);

        if ($request->search) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('ordinance_number', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $ordinances = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($ordinances);
    }

    // ================= STORE (AUTO ASSIGN USER_ID)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'ordinance_number' => 'nullable|string',
            'status' => 'nullable|string',
            'effectivity_date' => 'nullable|date',
            'approved_date' => 'nullable|date',
            'penalties' => 'nullable|string',
        ]);

        $ordinance = Ordinance::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'ordinance_number' => $request->ordinance_number,
            'status' => $request->status ?? 'active',
            'effectivity_date' => $request->effectivity_date,
            'approved_date' => $request->approved_date,
            'penalties' => $request->penalties,

            // 🔥 OWNERSHIP
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Ordinance created successfully',
            'data' => $ordinance
        ]);
    }

    // ================= SHOW (USER OWNED ONLY)
    public function show($id)
    {
        $user = auth()->user();

        $ordinance = Ordinance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json($ordinance);
    }

    // ================= UPDATE (USER OWNED ONLY)
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $ordinance = Ordinance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'sometimes|string',
            'description' => 'sometimes|string',
            'category' => 'nullable|string',
            'ordinance_number' => 'nullable|string',
            'status' => 'nullable|string',
            'effectivity_date' => 'nullable|date',
            'approved_date' => 'nullable|date',
            'penalties' => 'nullable|string',
        ]);

        $ordinance->update($validated);

        return response()->json([
            'message' => 'Ordinance updated successfully',
            'data' => $ordinance
        ]);
    }

    // ================= DELETE (USER OWNED ONLY)
    public function destroy($id)
    {
        $user = auth()->user();

        $ordinance = Ordinance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $ordinance->delete();

        return response()->json([
            'message' => 'Ordinance deleted successfully'
        ]);
    }
}
