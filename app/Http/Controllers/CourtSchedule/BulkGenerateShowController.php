<?php

namespace App\Http\Controllers\CourtSchedule;

use App\Http\Controllers\Controller;
use App\Models\Court;

class BulkGenerateShowController extends Controller
{
    public function __invoke()
    {
        $courts = Court::with('venue')->get();
        return inertia("CourtSchedule/BulkGenerate", [
            'courts' => $courts,
        ]);
    }
}
