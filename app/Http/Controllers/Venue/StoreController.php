<?php

namespace App\Http\Controllers\Venue;

use App\Models\Venue;
use App\Models\VenueFacility;
use App\Models\VenuePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
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
            'facilities' => 'nullable|array',
            'facilities.*.name' => 'required|string',
            'facilities.*.icon' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*.file' => 'required|string',
            'photos.*.is_primary' => 'nullable|boolean',
        ]);
        
        $venue = Venue::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'province' => $validated['province'],
            'city' => $validated['city'],
            'address' => $validated['address'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'average_rating' => $validated['average_rating'] ?? null,
            'slug' => Str::slug($validated['name']),
        ]);

        // Create facilities if provided
        if (!empty($validated['facilities'])) {
            foreach ($validated['facilities'] as $facility) {
                VenueFacility::create([
                    'venue_id' => $venue->id,
                    'name' => $facility['name'],
                    'icon' => $facility['icon'],
                ]);
            }
        }

        // Create photos if provided
        if (!empty($validated['photos'])) {
            // Find the primary photo if any
            $primaryPhotoIndex = null;
            foreach ($validated['photos'] as $index => $photo) {
                if ($photo['is_primary'] ?? false) {
                    $primaryPhotoIndex = $index;
                    break;
                }
            }

            foreach ($validated['photos'] as $index => $photo) {
                VenuePhoto::create([
                    'venue_id' => $venue->id,
                    'file' => $photo['file'],
                    'is_primary' => $index === $primaryPhotoIndex,
                ]);
            }
        }

        return response()->json(['message' => 'Venue created successfully']);
    }
}
