<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class FindController extends Controller
{
    public function __invoke(Role $role)
    {
        return new JsonResource($role);
    }
}
