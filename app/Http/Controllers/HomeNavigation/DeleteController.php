<?php

namespace App\Http\Controllers\HomeNavigation;

use App\Http\Controllers\Controller;
use App\Models\HomeNavigation;

class DeleteController extends Controller
{
    public function __invoke(HomeNavigation $homeNavigation)
    {
        $homeNavigation->delete();
        return response()->json(null, 204);
    }
}
