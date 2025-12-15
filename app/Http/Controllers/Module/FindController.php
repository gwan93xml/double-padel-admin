<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(Module $module)
    {
        return new JsonResource($module);
    }
}
