<?php

namespace App\Http\Controllers\UpdateCategory;

use App\Http\Controllers\Controller;
use App\Models\UpdateCategory;

class DeleteController extends Controller
{
    public function __invoke(UpdateCategory $updateCategory)
    {
        $updateCategory->delete();
        return response()->json(null, 204);
    }
}
