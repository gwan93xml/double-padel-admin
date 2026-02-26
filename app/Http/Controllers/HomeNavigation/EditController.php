<?php

namespace App\Http\Controllers\HomeNavigation;

use App\Http\Controllers\Controller;
use App\Models\HomeNavigation;

class EditController extends Controller
{
    public function __invoke(HomeNavigation $homeNavigation)
    {
        return inertia('HomeNavigation/Edit', [
            'homeNavigation' => $homeNavigation,
        ]);
    }
}
