<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Models\Update;
use App\Models\UpdateCategory;

class EditController extends Controller
{
    public function __invoke(Update $update)
    {
        $categories = UpdateCategory::all();

        return inertia('Update/Edit', [
            'update' => $update,
            'categories' => $categories,
        ]);
    }
}
