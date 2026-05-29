<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
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
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255', //complied
            'location' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'current_occupancy' => 'nullable|integer|min:0',

            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',

            'event_type' => 'nullable|string|max:255',


            'status' => 'nullable|in:Standby,Open,Full,Closed',
            'facilities' => 'nullable|array',
        ]);

        $validated['barangay'] = $user->barangay;
        $validated['created_by'] = $user->id;

        $center = EvacuationCenter::create($validated);

        SendAdminNotificationJob::dispatch(
            'resident',
            [
                'title' => "Barangay {$user->barangay}",
                'body' => "Barangay {$user->barangay} created evacuation center information!",
                'sms' => "[AlertoPH ALERT]\n Barangay {$user->barangay} posted evacuation center information!\n",
                'request_id' => $user->id,
                'url' => '/centers'
            ],
            $user->barangay
        );

        return response()->json([
            'message' => 'Evacuation center created successfully',
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
            'name' => 'required|string|max:255', //complied
            'location' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'current_occupancy' => 'nullable|integer|min:0',

            'contact_person' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',

            'event_type' => 'nullable|string|max:255',


            'status' => 'nullable|in:Standby,Open,Full,Closed',
            'facilities' => 'nullable|array',
        ]);

        $center->update($validated);

        SendAdminNotificationJob::dispatch(
            'resident',
            [
                'title' => "Barangay {$user->barangay}",
                'body' => "Barangay {$user->barangay} update evacuation center information!",
                'sms' => "[AlertoPH ALERT]\n Barangay {$user->barangay} update evacuation center information!\n",
                'request_id' => $user->id,
                'url' => '/centers'
            ],
            $user->barangay
        );


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
