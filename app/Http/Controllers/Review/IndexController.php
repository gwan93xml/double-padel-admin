<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("Review/Page");
    }
}
