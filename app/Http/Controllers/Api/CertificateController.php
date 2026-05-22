<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate as DocumentRequest;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * 📄 Get all requests (admin view)
     */
    public function index()
    {
        $data = DocumentRequest::with('user')
            ->latest()
            ->paginate(10);

        return response()->json($data);
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
}
