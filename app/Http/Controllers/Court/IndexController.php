<?php

namespace App\Http\Controllers\Court;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("Court/Page");
    }
}
