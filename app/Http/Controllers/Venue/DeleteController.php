<?php

namespace App\Http\Controllers\Venue;

use App\Models\Venue;

class DeleteController
{
    public function __invoke(Venue $venue)
    {
        $venue->delete();
        return response()->json(['message' => 'Venue deleted successfully']);
    }
}
