<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate as DocumentRequest;

class CertificateController extends Controller
{
    /**
     * 📄 Get all requests (admin view)
     */
    public function index()
    {
        return response()->json(
            DocumentRequest::with('user')->latest()->get()
        );
    }

    /**
     * ➕ Store new request (user submits form)
     */
    public function store(Request $request)
    {
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
        $request = DocumentRequest::findOrFail($id);
        $request->delete();

        return response()->json([
            'message' => 'Request deleted successfully'
        ]);
    }
}
