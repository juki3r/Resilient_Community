<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // GET ALL
    public function index()
    {
        return News::latest()->paginate(10);
    }

    // STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // 5MB limit
            'status' => 'nullable|in:draft,published',
        ]);

        // =====================
        // IMAGE UPLOAD
        // =====================
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $validated['image'] = $path;
        }

        // =====================
        // DEFAULT VALUES
        // =====================
        $validated['user_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'draft';

        // only set published_at if actually published
        $validated['published_at'] = $validated['status'] === 'published'
            ? now()
            : null;

        $news = News::create($validated);

        return response()->json([
            'message' => 'News created successfully',
            'data' => $news
        ], 201);
    }

    // SHOW
    public function show($id)
    {
        return News::findOrFail($id);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'image' => 'nullable|image|max:5120',
        ]);

        // =====================
        // IMAGE UPDATE
        // =====================
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $validated['image'] = $path;
        }

        // =====================
        // PUBLISHED LOGIC
        // =====================
        if (isset($validated['status']) && $validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $news->update($validated);

        return response()->json([
            'message' => 'News updated successfully',
            'data' => $news
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        News::destroy($id);

        return response()->json([
            'message' => 'News deleted'
        ]);
    }
}
