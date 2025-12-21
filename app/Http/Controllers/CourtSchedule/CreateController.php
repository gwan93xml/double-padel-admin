<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\User;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke()
    {
        $courts = Court::with('venue')->get();
        $users = User::all();
        return inertia("CourtSchedule/Create", [
            'courts' => $courts,
            'users' => $users,
        ]);
    }
}
