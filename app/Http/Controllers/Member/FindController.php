<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(User $user)
    {
        return new JsonResource($user);
    }
}
