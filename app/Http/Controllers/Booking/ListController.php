<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListController extends Controller
{
    public function __invoke(Request $request)
    {
        $bookings = Booking::query()
            ->with(['user', 'courtSchedule.court.venue'])
            ->when($request->search, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('booking_number', 'like', "%$keyword%");
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                $query->orderBy($request->sort, $request->order);
            })
            ->when($request->start_date && !$request->end_date, function ($query) use ($request) {
                $query->whereHas('courtSchedule', function ($query) use ($request) {
                    $query->where('date', '>=', $request->start_date);
                });
            })
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $query->whereHas('courtSchedule', function ($query) use ($request) {
                    $query->whereBetween('date', [$request->start_date, $request->end_date]);
                });
            })
            ->when(!$request->start_date && $request->end_date, function ($query) use ($request) {
                $query->whereHas('courtSchedule', function ($query) use ($request) {
                    $query->where('date', '<=', $request->end_date);
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->paginate($request->take);
        return new JsonResource($bookings);
    }
}
