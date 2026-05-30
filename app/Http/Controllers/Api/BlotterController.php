<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Blotter;
use App\Models\MobileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class BlotterController extends Controller
{
    // =========================
    // GET ALL BLOTTERS
    // =========================
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $query = Blotter::with(['complainant', 'respondent'])
            ->where('barangay', $user->barangay)
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('incident_type', 'like', "%{$request->search}%")
                    ->orWhere('incident_location', 'like', "%{$request->search}%")
                    ->orWhere('incident_details', 'like', "%{$request->search}%")
                    ->orWhere('complainant_name', 'like', "%{$request->search}%");
            });
        }

        return $query->paginate(10);
    }

    //Mobile app
    public function index_appuser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $requests = Blotter::where('complainant_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }

    // =========================
    // STORE NEW BLOTTER
    // =========================
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'incident_type' => 'required|string',
    //         'incident_category' => 'nullable|string',
    //         'incident_date' => 'required|date',
    //         'incident_time' => 'nullable',
    //         'incident_location' => 'required|string',
    //         'incident_details' => 'required|string',

    //         'complainant_id' => 'nullable|exists:residents,id',
    //         'complainant_name' => 'required|string',

    //         'respondent_id' => 'nullable|exists:residents,id',
    //         'respondent_name' => 'nullable|string',

    //         'status' => 'nullable|string',
    //         'priority_level' => 'nullable|string',
    //     ]);

    //     DB::transaction(function () use (&$validated, &$blotter) {

    //         $year = date('Y');

    //         // GET LAST NUMBER FOR THIS YEAR
    //         $last = Blotter::whereYear('created_at', $year)
    //             ->orderBy('id', 'desc')
    //             ->first();

    //         $nextNumber = 1;

    //         if ($last && $last->blotter_number) {
    //             $parts = explode('-', $last->blotter_number);
    //             $nextNumber = intval(end($parts)) + 1;
    //         }

    //         $validated['blotter_number'] =
    //             'BLT-' . $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

    //         $validated['status'] = $validated['status'] ?? 'Pending';
    //         $validated['priority_level'] = $validated['priority_level'] ?? 'Medium';

    //         $blotter = Blotter::create($validated);
    //     });

    //     return response()->json([
    //         'message' => 'Blotter created successfully',
    //         'data' => $blotter
    //     ], 201);
    // }
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

        $blotter = null;

        DB::transaction(function () use (&$validated, &$blotter, $request) {

            $year = date('Y');

            $last = Blotter::whereYear('created_at', $year)
                ->lockForUpdate()
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
            $validated['user_id'] = auth()->user()->id;
            $validated['barangay'] = auth()->user()->barangay;

            $blotter = Blotter::create($validated);
        });

        return response()->json([
            'message' => 'Blotter created successfully',
            'data' => $blotter
        ], 201);
    }

    public function store_appuser(Request $request)
    {



        $validated = $request->validate([
            'incident_type' => 'required|string', //complied
            'incident_category' => 'nullable|string',
            'incident_date' => 'required|date', //complied
            'incident_time' => 'nullable',
            'incident_location' => 'required|string', //complied
            'incident_details' => 'required|string', //complied

            'complainant_id' => 'nullable|exists:residents,id',
            'complainant_name' => 'required|string', //complied

            'respondent_id' => 'nullable|exists:residents,id',
            'respondent_name' => 'nullable|string', //complied

            'status' => 'nullable|string',
            'priority_level' => 'nullable|string',
        ]);

        $blotter = null;

        DB::transaction(function () use (&$validated, &$blotter, $request) {
            $mobileuser = auth()->user();

            $year = date('Y');

            $last = Blotter::whereYear('created_at', $year)
                ->lockForUpdate()
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
            $validated['complainant_id'] = $mobileuser->id;
            $validated['complainant_contact'] = $mobileuser->phone;
            $validated['barangay'] = $mobileuser->barangay;

            $blotter = Blotter::create($validated);


            // ========= This section will alert admin that user request certifications =======
            // ========= Use FCM admin app, Sms to notify admin ===============================


            // ONLY THIS LINE (NO LOOPS, NO SMS, NO FIREBASE HERE)
            // NotifyAdminsJob::dispatch($documentRequest->id);

            SendAdminNotificationJob::dispatch(
                'admin',
                [
                    'title' => 'New Blotter Report',
                    'body' => "New blotter from {$request->complainant_name}",
                    'sms' => "[AlertoPH ALERT]\n{$request->complainant_name} submitted a blotter report!",
                    'request_id' => $request->complainant_id,
                    'url' => '/blotters'
                ],
                $mobileuser->barangay
            );
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


        // ✅ FIXED USER LOOKUP
        $user = MobileUser::find($blotter->complainant_id);

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        $title = "Blotter report Update";
        $body  = "Your blotter report has been " . $request->status;

        // ================= FCM =================
        if ($user->fcm_token) {
            (new \App\Services\FirebaseService)->sendNotification(
                $user->fcm_token,
                $title,
                $body,
                [
                    'screen' => 'Requests',
                    'requests_id' => (string) $blotter->id,
                ]
            );
        }

        // ================= SMS =================
        try {
            if ($user->phone) {
                Http::withHeaders([
                    'X-API-KEY' => env('SMS_API_KEY')
                ])->post('https://carlesppo.com/api/send-sms-api', [
                    'phone_number' => $user->phone,
                    'message' => "[AlertoPH ALERT]\n$title\n$body"
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('SMS failed: ' . $e->getMessage());
        }

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
