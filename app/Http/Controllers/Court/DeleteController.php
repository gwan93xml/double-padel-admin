<?php

namespace App\Http\Controllers\Court;

use App\Http\Controllers\Controller;
use App\Models\Court;

class DeleteController extends Controller
{
    public function __invoke(Court $court)
    {
        $court->delete();
        return response()->json(['message' => 'Court deleted successfully']);
    }
}
