<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Spatie\Permission\Models\Role;

class EditController extends Controller
{
    public function __invoke(Role $role)
    {
        return inertia('Role/Edit', [
            'role' => $role->load('permissions'),
            'modules' => Module::with(['actions'])->get(),
        ]);
    }
}
