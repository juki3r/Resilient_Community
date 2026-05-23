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
            'title' => 'required|string',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'image' => 'nullable|image',
            'status' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $validated['user_id'] = auth()->id();
        $validated['published_at'] = now();

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

        $news->update($request->all());

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
