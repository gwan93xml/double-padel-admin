<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Models\UpdateCategory;

class CreateController extends Controller
{
    public function __invoke()
    {
        $categories = UpdateCategory::all();

        return inertia('Update/Create', [
            'categories' => $categories,
        ]);
    }
}
