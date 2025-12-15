<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchByCodeController extends Controller
{
    public function __invoke(Request $request)
    {
        $action = Action::query()
            ->where('code', 'like', "%{$request->search}%")
            ->first();
        return new JsonResource($action);
    }
}
