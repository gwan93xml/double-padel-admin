<?php

namespace App\Http\Controllers\Court;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;

class CreateController extends Controller
{
    public function __invoke()
    {
        $venues = Venue::all();
        return inertia("Court/Create", [
            'venues' => $venues,
        ]);
    }
}
