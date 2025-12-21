<?php

namespace App\Http\Controllers\Court;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Court $court)
    {
        $validated = $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'name' => 'required|string',
            'court_type' => 'required|string',
            'price_per_hour' => 'required|integer|min:0',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,maintenance,closed',
            'image' => 'nullable|string',
        ]);

        $court->update($validated);

        return response()->json(['message' => 'Court updated successfully']);
    }
}
