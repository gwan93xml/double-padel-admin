<?php

namespace App\Http\Controllers\UpdateCategory;

use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    public function __invoke()
    {
        return inertia('UpdateCategory/Create');
    }
}
