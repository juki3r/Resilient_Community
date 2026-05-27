<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAdminNotificationJob;
use App\Models\Certificate as DocumentRequest;
use App\Models\MobileUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CertificateController extends Controller
{
    /**
     * 📄 Get all requests (admin view)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $query = DocumentRequest::with('user')
            ->where('barangay', $user->barangay)
            ->orderByRaw("
            CASE 
                WHEN status = 'pending' THEN 0
                WHEN status = 'approved' THEN 1
                WHEN status = 'rejected' THEN 2
                ELSE 3
            END
        ")
            ->orderBy('created_at', 'desc'); // proper fallback

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                    ->orWhere('document_type', 'like', "%$search%")
                    ->orWhere('purpose', 'like', "%$search%");
            });
        }

        return response()->json($query->paginate(10));
    }


    /**
     * ➕ Store new request (user submits form)
     */
    public function store(Request $request)
    {
        // $user = User::find($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'address' => 'required|string',

            'document_type' => 'required|string',
            'purpose' => 'required|string',

            'company_name' => 'nullable|string|max:255',
            'business_nature' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();

        $documentRequest = DocumentRequest::create($validated);

        return response()->json([
            'message' => 'Request submitted successfully',
            'data' => $documentRequest
        ]);
    }





    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $certificate = DocumentRequest::findOrFail($id);

        $certificate->update([
            'status' => $request->status,
        ]);

        // ✅ FIXED USER LOOKUP
        $user = MobileUser::find($certificate->mobile_user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        $title = "Certification Request Update";
        $body  = "Your request has been " . $request->status;

        // ================= FCM =================
        if ($user->fcm_token) {
            (new \App\Services\FirebaseService)->sendNotification(
                $user->fcm_token,
                $title,
                $body,
                [
                    'screen' => 'Requests',
                    'requests_id' => (string) $certificate->id,
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
            'message' => 'Status updated successfully',
            'data' => $certificate
        ]);
    }

    /**
     * 👁️ Show single request
     */
    public function show($id)
    {
        return response()->json(
            DocumentRequest::with('user')->findOrFail($id)
        );
    }

    /**
     * ❌ Delete request
     */
    public function destroy($id)
    {
        $certificate = DocumentRequest::findOrFail($id);
        $certificate->delete();

        return response()->json([
            'message' => 'Deleted successfully'
        ]);
    }



    // ======================== Mobile  certificate request =======================


    public function index_appuser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $requests = DocumentRequest::where('barangay', $user->barangay)
            ->where('mobile_user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'requests' => $requests
        ]);
    }



    public function store_appuser(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'address' => 'required|string',

            'document_type' => 'required|string',
            'purpose' => 'required|string',

            'company_name' => 'nullable|string|max:255',
            'business_nature' => 'nullable|string|max:255',
        ]);

        $validated['mobile_user_id'] = $user->id;
        $validated['barangay'] = $user->barangay;

        $documentRequest = DocumentRequest::create($validated);

        // ========= This section will alert admin that user request certifications =======
        // ========= Use FCM admin app, Sms to notify admin ===============================


        // ONLY THIS LINE (NO LOOPS, NO SMS, NO FIREBASE HERE)
        // NotifyAdminsJob::dispatch($documentRequest->id);

        // SendAdminNotificationJob::dispatch(
        //     'certificate',
        //     [
        //         'title' => 'New Certification Request',
        //         'body' => "New request from {$documentRequest->full_name}",
        //         'sms' => "[AlertoPH ALERT]\n{$documentRequest->full_name} requested {$documentRequest->document_type}",
        //         'request_id' => $documentRequest->id
        //     ],
        //     $user->barangay
        // );


        //====================================================================================

        return response()->json([
            'message' => 'Request submitted successfully',
        ]);
    }
}
