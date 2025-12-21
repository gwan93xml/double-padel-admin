<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;

class CreateController extends Controller
{
    public function __invoke()
    {
        $categories = BlogCategory::all();
        return inertia('Blog/Create', [
            'categories' => $categories
        ]);
    }
}
