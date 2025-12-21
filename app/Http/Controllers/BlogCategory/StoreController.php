<?php

namespace App\Http\Controllers\BlogCategory;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:blog_categories',
        ]);

        $blogCategory = BlogCategory::create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);
        return new JsonResource($blogCategory);
    }
}
