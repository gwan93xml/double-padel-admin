<?php

namespace App\Http\Controllers\Court;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\Venue;
use Illuminate\Http\Request;

class EditController extends Controller
{
    public function __invoke(Court $court)
    {
        $venues = Venue::all();
        return inertia("Court/Edit", [
            'court' => $court->load('venue'),
            'venues' => $venues,
        ]);
    }
}
