<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListController extends Controller
{
    public function __invoke(Request $request)
    {
        $itemCategories = Module::query()
            ->when($request->search, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%$keyword%");
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order);
            })
            ->with(['actions' => function ($query) {
                $query->select('id', 'name');
            }])
            ->paginate($request->take);
        return new JsonResource($itemCategories);
    }
}
