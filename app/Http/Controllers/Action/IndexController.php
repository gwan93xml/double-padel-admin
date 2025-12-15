<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("Action/Page");
    }
}
