<?php

namespace App\Http\Controllers\Testimony;

use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    public function __invoke()
    {
        return inertia('Testimony/Create');
    }
}
