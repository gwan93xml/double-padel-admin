<?php

namespace App\Http\Controllers\HomeNavigation;

use App\Http\Controllers\Controller;
use App\Models\HomeNavigation;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(HomeNavigation $homeNavigation)
    {
        return new JsonResource($homeNavigation);
    }
}
