<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateRequest;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(UpdateRequest $request, User $user)
    {
        $user->update([
            ...$request->validated(),
            'password' => $request->password ? bcrypt($request->password) : $user->password,
        ]);
        $user->syncRoles($request->roles);
        return new JsonResource($user);
    }
}
