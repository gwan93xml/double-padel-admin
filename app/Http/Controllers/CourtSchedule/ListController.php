<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListController extends Controller
{
    public function __invoke(Request $request)
    {
        $schedules = CourtSchedule::query()
            ->with(['court.venue', 'user'])
            ->when($request->search, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('court', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    })
                    ->orWhereHas('court.venue', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    })
                    ->orWhereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%$keyword%");
                    });
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order);
            })
            ->paginate($request->take);
        return new JsonResource($schedules);
    }
}
