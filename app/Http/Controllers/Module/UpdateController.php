<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module\UpdateRequest;
use App\Models\Module;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(UpdateRequest $request, Module $module)
    {
        $module->update([
            ...$request->validated(),
            'slug' => str()->slug($request->name),
        ]);
        $module->actions()->sync($request->actions);
        return new JsonResource($module);
    }
}
