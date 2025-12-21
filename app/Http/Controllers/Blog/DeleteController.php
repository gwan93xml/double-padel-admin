<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class DeleteController extends Controller
{
    public function __invoke(Blog $blog)
    {
        $blog->delete();
        return response()->json(null, 204);
    }
}
