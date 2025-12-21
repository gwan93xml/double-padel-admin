<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'user_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'price' => 'required|integer|min:0',
            'status' => 'required|in:available,booked,closed',
        ]);
        $courtSchedule = CourtSchedule::create($validated);
        $courtSchedule->court->venue->updatePriceRange();
        return response()->json(['message' => 'Court Schedule created successfully']);
    }
}
