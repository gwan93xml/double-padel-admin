<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(Request $request, CourtSchedule $courtSchedule)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'user_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'price' => 'required|integer|min:0',
            'status' => 'required|in:available,booked,closed',
        ]);

        $courtSchedule->update($validated);
        $courtSchedule->court->venue->updatePriceRange();
        return new JsonResource($courtSchedule);

    }
}
