<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class ListController extends Controller
{
    public function __invoke(Request $request)
    {
        $itemCategories = Role::query()
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
