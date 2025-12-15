<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Module;

class CreateController extends Controller
{
    public function __invoke()
    {
        $modules = Module::with('actions')->get();
        return inertia('Role/Create', [
            'modules' => $modules
        ]);
    }
}
