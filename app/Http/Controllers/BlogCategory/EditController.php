<?php

namespace App\Http\Controllers\BlogCategory;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;

class EditController extends Controller
{
    public function __invoke(BlogCategory $blogCategory)
    {
        return inertia('BlogCategory/Edit', [
            'blogCategory' => $blogCategory
        ]);
    }
}
