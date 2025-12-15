<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("Role/Page");
    }
}
