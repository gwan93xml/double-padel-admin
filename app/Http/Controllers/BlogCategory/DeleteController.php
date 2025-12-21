<?php

namespace App\Http\Controllers\BlogCategory;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;

class DeleteController extends Controller
{
    public function __invoke(BlogCategory $blogCategory)
    {
        $blogCategory->delete();
        return response()->json(null, 204);
    }
}
