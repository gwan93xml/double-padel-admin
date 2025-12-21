<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\CourtSchedule;
use App\Models\Court;
use App\Models\User;
use Illuminate\Http\Request;

class EditController extends Controller
{
    public function __invoke(CourtSchedule $courtSchedule)
    {
        $courts = Court::with('venue')->get();
        $users = User::all();
        return inertia("CourtSchedule/Edit", [
            'courtSchedule' => $courtSchedule->load(['court.venue', 'user']),
            'courts' => $courts,
            'users' => $users,
        ]);
    }
}
