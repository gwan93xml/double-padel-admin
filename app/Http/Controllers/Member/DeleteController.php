<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;

class DeleteController extends Controller
{
    public function __invoke(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
