<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia('Update/Page');
    }
}
