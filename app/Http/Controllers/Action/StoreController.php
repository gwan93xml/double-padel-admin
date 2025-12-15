<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Http\Requests\Action\StoreRequest;
use App\Models\Action;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreController extends Controller
{
    public function __invoke(StoreRequest $request)
    {
        $action = Action::create([
            ...$request->validated(),
        ]);
        return new JsonResource($action);
    }
}
