<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchByNameController extends Controller
{
    public function __invoke(Request $request)
    {
        $action = Action::query()
            ->where('name', 'like', "%{$request->search}%")
            ->first();
        return new JsonResource($action);
    }
}
