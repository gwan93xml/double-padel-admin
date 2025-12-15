<?php

namespace App\Http\Controllers\Action;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListController extends Controller
{
    public function __invoke(Request $request)
    {
        $itemCategories = Action::query()
            ->when($request->search, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order);
            })
            ->paginate($request->take);
        return new JsonResource($itemCategories);
    }
}
