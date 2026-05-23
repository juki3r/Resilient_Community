<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\Http\Request;

class OfficialController extends Controller
{
    // GET ALL (with search + pagination)
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Official::where('barangay', $user->barangay);

        if ($request->search) {
            $query->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('position', 'like', "%{$request->search}%");
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    // STORE
    public function store(Request $request)
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $barangay = Official::where('barangay', $user->barangay);


        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'position' => 'required|string',
            'committee' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',

            'term_start' => 'nullable|date',
            'term_end' => 'nullable|date',

            'status' => 'nullable|in:active,inactive,former',

            'remarks' => 'nullable|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        // =====================
        // PHOTO UPLOAD (same style)
        // =====================
        if ($request->hasFile('photo')) {

            $file = $request->file('photo');

            $destination = public_path('uploads/officials');

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $validated['photo'] = 'uploads/officials/' . $filename;
        }

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['barangay'] = $barangay;
        $official = Official::create($validated);

        return response()->json([
            'message' => 'Official created successfully',
            'data' => $official
        ], 201);
    }

    // SHOW
    public function show($id)
    {
        return Official::findOrFail($id);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $official = Official::findOrFail($id);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('officials', 'public');
            $data['photo'] = $path;
        }

        $official->update($data);

        return response()->json([
            'message' => 'Official updated',
            'data' => $official
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        Official::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Official deleted'
        ]);
    }
}
