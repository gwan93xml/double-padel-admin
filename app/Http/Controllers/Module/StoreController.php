<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Http\Requests\Module\StoreRequest;
use App\Models\Module;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        $module = Module::create([
            ...$request->validated(),
            'slug' => str()->slug($request->name),
        ]);
        $module->actions()->attach($request->actions);
        return new JsonResource($module);
    }
}
