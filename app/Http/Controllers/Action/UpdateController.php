<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Http\Requests\Action\UpdateRequest;
use App\Models\Action;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(UpdateRequest $request, Action $action)
    {
        $action->update([
            ...$request->validated(),
        ]);
        return new JsonResource($action);
    }
}
