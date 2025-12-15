<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Action;

class EditController extends Controller
{
    public function __invoke(Action $action)
    {
        return inertia('Action/Edit', [
            'action' => $action
        ]);
    }
}
