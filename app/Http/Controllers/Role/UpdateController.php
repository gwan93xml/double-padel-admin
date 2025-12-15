<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\UpdateRequest;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdateController extends Controller
{
    public function __invoke(UpdateRequest $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $role->update([
                ...$request->validated(),
            ]);
            $role->syncPermissions([]);
            foreach ($request->permissions as $permission) {
                $_permission = Permission::findOrCreate($permission);
                $_permissions[] = $_permission->id;
            }
            $role->syncPermissions($_permissions ?? []);
            DB::commit();
            return new JsonResource($role);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
