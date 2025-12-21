<?php

namespace App\Http\Controllers\BlogCategory;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UpdateController extends Controller
{
    public function __invoke(Request $request, BlogCategory $blogCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:blog_categories,name,' . $blogCategory->id,
        ]);

        $blogCategory->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);
        return new JsonResource($blogCategory);
    }
}
