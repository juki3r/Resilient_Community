<?php
// app/Http/Controllers/Api/OrdinanceController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Ordinance;
use App\Models\User;
use Illuminate\Http\Request;

class OrdinanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Ordinance::where('barangay', $user->barangay);

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

    //This is MOBILE APP
    public function index_appuser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $ordinances = Ordinance::where('barangay', $user->barangay)
            ->latest()
            ->get();

        return response()->json([
            'ordinances' => $ordinances
        ]);
    }

    // ================= STORE (AUTO ASSIGN USER_ID)
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
            'effectivity_date' => 'nullable|date',
            'approved_date' => 'nullable|date',
            'penalties' => 'nullable|string',
        ]);

        // 🔥 AUTO FORMAT: ORD-2026-001
        $year = date('Y');

        $count = Ordinance::whereYear('created_at', now()->year)->count() + 1;

        $ordinanceNumber =
            'ORD-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $ordinance = Ordinance::create([
            'barangay' => $user->barangay,
            'ordinance_number' => $ordinanceNumber,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'effectivity_date' => $validated['effectivity_date'] ?? null,
            'approved_date' => $validated['approved_date'] ?? null,
            'penalties' => $validated['penalties'] ?? null,
        ]);

        // =========================
        // NOTIFICATION (ASYNC JOB)
        // =========================
        SendAdminNotificationJob::dispatch(
            'resident',
            [
                'title' => "Barangay {$user->barangay}!",
                'body' => "Barangay {$user->barangay} added new ordinance!",
                'sms' => "[AlertoPH ALERT]\n Barangay {$user->barangay} added new ordinance!\n",
                'request_id' => $user->id,
                'url' => '/ordinance'
            ],
            $user->barangay
        );

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
        $ordinance = Ordinance::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'nullable|string',
            'effectivity_date' => 'nullable|date',
            'approved_date' => 'nullable|date',
            'penalties' => 'nullable|string',
        ]);

        $ordinance->update($validated);

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $ordinance
        ]);
    }

    // ================= DELETE (USER OWNED ONLY)
    // public function destroy($id)
    // {
    //     $user = auth()->user();

    //     $ordinance = Ordinance::where('id', $id)
    //         ->where('barangay', $user->barangay)
    //         ->firstOrFail();

    //     $ordinance->delete();

    //     return response()->json([
    //         'message' => 'Ordinance deleted successfully'
    //     ]);
    // }

    public function destroy($id)
    {
        $ordinance = Ordinance::findOrFail($id);
        $ordinance->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }
}
