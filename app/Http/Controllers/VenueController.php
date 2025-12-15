<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VenueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $venues = Venue::paginate(15);
        return Inertia::render('Venue/Index', [
            'venues' => $venues,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Venue/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:venues',
            'name' => 'required|string',
            'description' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'average_rating' => 'nullable|numeric|between:0,5',
        ]);

        Venue::create($validated);

        return response()->json(['message' => 'Venue created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Venue $venue)
    {
        return Inertia::render('Venue/Show', [
            'venue' => $venue,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Venue $venue)
    {
        return Inertia::render('Venue/Edit', [
            'venue' => $venue,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:venues,slug,' . $venue->id,
            'name' => 'required|string',
            'description' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'average_rating' => 'nullable|numeric|between:0,5',
        ]);

        $venue->update($validated);

        return response()->json(['message' => 'Venue updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Venue $venue)
    {
        $venue->delete();
        return response()->json(['message' => 'Venue deleted successfully']);
    }
}
