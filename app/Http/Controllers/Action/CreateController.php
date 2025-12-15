<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    public function __invoke()
    {
        return inertia('Action/Create');
    }
}
