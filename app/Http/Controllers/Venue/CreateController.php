<?php

namespace App\Http\Controllers\Venue;

use Inertia\Inertia;

class CreateController
{
    public function __invoke()
    {
        return Inertia::render('Venue/Create');
    }
}
