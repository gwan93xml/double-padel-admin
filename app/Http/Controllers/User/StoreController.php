<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreRequest;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        $user = User::create([
            ...$request->validated(),
            'user_type' => User::USER_TYPE_ADMIN,
            'password' => bcrypt($request->password),
        ]);
        $user->syncRoles($request->roles);
        return new JsonResource($user);
    }
}
