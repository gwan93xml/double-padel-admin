<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __invoke(Request $request)
    {
        $courtId = $request->get('court_id');
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $courts = Court::with('venue')->get();

        $schedules = [];
        if ($courtId) {
            $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $endDate = date('Y-m-t', strtotime($startDate));

            $schedules = CourtSchedule::where('court_id', $courtId)
                ->whereRaw("DATE(date) >= ?", [$startDate])
                ->whereRaw("DATE(date) <= ?", [$endDate])
                ->with('court.venue', 'user')
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();
        }

        return inertia("CourtSchedule/Calendar", [
            'courts' => $courts,
            'schedules' => $schedules,
            'selectedCourtId' => $courtId ? (int)$courtId : null,
            'currentMonth' => $month,
            'currentYear' => $year,
        ]);
    }
}
