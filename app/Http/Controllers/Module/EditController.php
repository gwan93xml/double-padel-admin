<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Module;

class EditController extends Controller
{
    public function __invoke(Module $module)
    {
        return inertia('Module/Edit', [
            'module' => $module->load('actions'),
            'actions' => Action::all(),
        ]);
    }
}
