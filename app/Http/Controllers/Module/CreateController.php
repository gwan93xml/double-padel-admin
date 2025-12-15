<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Action;

class CreateController extends Controller
{
    public function __invoke()
    {
        $actions = Action::all();
        return inertia('Module/Create', [
            'actions' => $actions
        ]);
    }
}
