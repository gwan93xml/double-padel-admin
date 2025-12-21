<?php

namespace App\Http\Controllers\Venue;

use App\Models\Venue;
use Inertia\Inertia;

class EditController
{
    public function __invoke(Venue $venue)
    {
        return Inertia::render('Venue/Edit', [
            'venue' => $venue->load(['photos', 'facilities']),
        ]);
    }
}
