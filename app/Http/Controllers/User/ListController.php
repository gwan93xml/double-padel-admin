<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListController extends Controller
{
    public function __invoke(Request $request)
    {
        $itemCategories = User::query()
            ->admin()
            ->when($request->search, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('email', 'like', "%$keyword%");
                    $query->orWhere('name', 'like', "%$keyword%");
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order);
            })
            ->with('roles')
            ->paginate($request->take);
        return new JsonResource($itemCategories);
    }
}
