<?php

namespace App\Http\Controllers\api;

use App\Models\Announcement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,urgent,emergency',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:draft,scheduled,published',
            'is_pinned' => 'boolean',
            'publish_at' => 'nullable|date',
        ]);

        $announcement = Announcement::create([
            ...$validated,
            'user_id' => auth()->id(), // 👈 IMPORTANT: who posted it
            'published_at' => $validated['status'] === 'published'
                ? now()
                : null,
        ]);

        return response()->json([
            'message' => 'Announcement created',
            'data' => $announcement->load('user'),
        ]);
    }

    public function index(Request $request)
    {
        $query = Announcement::with('user')
            ->latest();

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $announcements = $query->paginate($request->per_page ?? 10);

        return response()->json($announcements);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $announcement->update($request->all());

        return response()->json([
            'message' => 'Updated successfully',
            'data' => $announcement->load('user'),
        ]);
    }

    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Deleted successfully',
        ]);
    }
}
