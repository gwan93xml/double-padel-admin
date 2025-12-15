<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;

class EditController extends Controller
{
    public function __invoke(User $user)
    {
        return inertia('User/Edit', [
            'user' => $user->load('roles'),
            'roles' => Role::all(),
        ]);
    }
}
