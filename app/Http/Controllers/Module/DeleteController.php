<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Module;

class DeleteController extends Controller
{
    public function __invoke(Module $module)
    {
        $module->actions()->detach();
        $module->delete();
        return response()->json(null, 204);
    }
}
