<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|string|unique:blogs,title,' . $blog->id,
            'content' => 'required|string',
            'image' => 'nullable|string',
            'tags' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $blog->update($validated);
        return new JsonResource($blog);
    }
}
