<?php

namespace App\Http\Controllers\BlogCategory;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("BlogCategory/Page");
    }
}
