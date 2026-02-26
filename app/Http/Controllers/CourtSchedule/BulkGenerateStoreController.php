<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;

class BulkGenerateStoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'status' => 'required|in:available,booked,closed',
            'time_slots' => 'required|array|min:1',
            'time_slots.*' => 'required|array',
            'time_slots.*.date' => 'required|date',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => 'required|after:time_slots.*.start_time',
            'time_slots.*.price' => 'required|integer|min:0',
            'time_slots.*.normal_price' => 'nullable|integer|min:0',
        ]);

        $schedules = [];
        $skipped = 0;
        
        foreach ($validated['time_slots'] as $slot) {
            // Check if schedule already exists with same court_id, date, start_time, and end_time
            $exists = CourtSchedule::where('court_id', $validated['court_id'])
                ->where('date', $slot['date'])
                ->where('start_time', $slot['start_time'])
                ->where('end_time', $slot['end_time'])
                ->exists();
            
            if (!$exists) {
                $schedules[] = [
                    'court_id' => $validated['court_id'],
                    'user_id' => null,
                    'date' => $slot['date'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'price' => $slot['price'],
                    'normal_price' => $slot['normal_price'] ?? $slot['price'],
                    'status' => $validated['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } else {
                $skipped++;
            }
        }
        

        if (count($schedules) > 0) {
            CourtSchedule::insert($schedules);
        }

        $message = 'Court schedules generated successfully. Total created: ' . count($schedules);
        if ($skipped > 0) {
            $message .= ' (Skipped ' . $skipped . ' duplicate(s))';
        }

        $court = Court::find($validated['court_id']);
        $court->venue->updatePriceRange();
        return redirect()->route('court-schedule.index')->with('success', $message);
    }
}
