<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;

class YearController extends Controller
{
    public function __invoke(Request $request)
    {
        $courtId = $request->get('court_id');
        $year = (int) $request->get('year', now()->year);

        $courts = Court::with('venue')->get();

        $monthlySchedules = [];
        if ($courtId) {
            // Get all schedules for the selected court and year
            $startDate = "{$year}-01-01";
            $endDate = "{$year}-12-31";

            $schedules = CourtSchedule::where('court_id', $courtId)
                ->whereRaw("DATE(date) >= ?", [$startDate])
                ->whereRaw("DATE(date) <= ?", [$endDate])
                ->with('court.venue', 'user')
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            // Group schedules by month
            foreach ($schedules as $schedule) {
                $month = date('n', strtotime($schedule->date));
                if (!isset($monthlySchedules[$month])) {
                    $monthlySchedules[$month] = [
                        'available' => 0,
                        'booked' => 0,
                        'closed' => 0,
                        'total' => 0,
                        'waiting_for_payment' => 0,
                    ];
                }
                $monthlySchedules[$month]['total']++;
                $monthlySchedules[$month][$schedule->status]++;
            }
        }

        return inertia("CourtSchedule/Year", [
            'courts' => $courts,
            'monthlySchedules' => $monthlySchedules,
            'schedules' => $schedules,
            'selectedCourtId' => $courtId ? (int)$courtId : null,
            'currentYear' => $year,
        ]);
    }
}
