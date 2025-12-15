<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;

class CreateController extends Controller
{
    public function __invoke()
    {
        return inertia('User/Create', [
            'roles' => Role::all()
        ]);
    }
}
