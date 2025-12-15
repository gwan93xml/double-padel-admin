<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRequest;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                ...$request->validated(),
            ]);
            foreach ($request->permissions as $permission) {
                $_permission = Permission::findOrCreate($permission);
                $role->givePermissionTo($_permission);
            }

            DB::commit();
            return new JsonResource($role);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
