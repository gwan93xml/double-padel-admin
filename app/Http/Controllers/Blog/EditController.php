<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;

class EditController extends Controller
{
    public function __invoke(Blog $blog)
    {
        $categories = BlogCategory::all();
        return inertia('Blog/Edit', [
            'blog' => $blog,
            'categories' => $categories
        ]);
    }
}
