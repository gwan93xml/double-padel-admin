<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("Module/Page");
    }
}
