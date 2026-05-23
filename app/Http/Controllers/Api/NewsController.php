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
            'image' => 'nullable|image|max:5120',
            'status' => 'nullable|in:draft,published',
        ]);

        // =====================
        // IMAGE UPLOAD (SAFE VERSION)
        // =====================
        if ($request->hasFile('image')) {

            $file = $request->file('image');

            $destination = public_path('uploads/news');

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $validated['image'] = 'uploads/news/' . $filename;
        }

        // =====================
        // DEFAULT VALUES
        // =====================
        $validated['user_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'draft';

        $validated['published_at'] =
            $validated['status'] === 'published' ? now() : null;

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
        // IMAGE UPDATE (SAFE)
        // =====================
        if ($request->hasFile('image')) {

            // delete old image
            if ($news->image && file_exists(public_path($news->image))) {
                unlink(public_path($news->image));
            }

            $file = $request->file('image');

            $destination = public_path('uploads/news');

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($destination, $filename);

            $validated['image'] = 'uploads/news/' . $filename;
        }

        // =====================
        // PUBLISH LOGIC
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
