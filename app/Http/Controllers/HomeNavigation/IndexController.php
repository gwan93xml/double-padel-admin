<?php

namespace App\Http\Controllers\HomeNavigation;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia('HomeNavigation/Page');
    }
}
