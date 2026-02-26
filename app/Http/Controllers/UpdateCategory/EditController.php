<?php

namespace App\Http\Controllers\UpdateCategory;

use App\Http\Controllers\Controller;
use App\Models\UpdateCategory;

class EditController extends Controller
{
    public function __invoke(UpdateCategory $updateCategory)
    {
        return inertia('UpdateCategory/Edit', [
            'updateCategory' => $updateCategory,
        ]);
    }
}
