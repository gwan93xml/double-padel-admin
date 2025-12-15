<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(Action $action)
    {
        return new JsonResource($action);
    }
}
