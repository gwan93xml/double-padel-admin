<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\CourtSchedule;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __invoke(Request $request, CourtSchedule $courtSchedule)
    {
        $courtSchedule->delete();
        return redirect()->route('court-schedule.index')->with('success', 'Court schedule deleted successfully.');
    }
}
