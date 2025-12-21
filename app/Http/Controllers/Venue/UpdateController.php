<?php

namespace App\Http\Controllers\Venue;

use App\Models\Venue;
use App\Models\VenueFacility;
use App\Models\VenuePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UpdateController
{
    public function __invoke(Request $request, Venue $venue)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:venues,name,' . $venue->id,
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
            'facilities.*.id' => 'nullable|integer|exists:venue_facilities,id',
            'facilities.*.name' => 'required|string',
            'facilities.*.icon' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*.id' => 'nullable|integer|exists:venue_photos,id',
            'photos.*.file' => 'required|string',
            'photos.*.is_primary' => 'nullable|boolean',
        ]);

        $venue->update([
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

        // Update facilities
        if (!empty($validated['facilities'])) {
            // Get existing facility IDs
            $existingIds = collect($validated['facilities'])
                ->pluck('id')
                ->filter()
                ->toArray();
            
            // Delete facilities that are not in the update request
            $venue->facilities()
                ->whereNotIn('id', $existingIds)
                ->delete();

            // Update or create facilities
            foreach ($validated['facilities'] as $facility) {
                if (isset($facility['id']) && $facility['id']) {
                    // Update existing facility
                    VenueFacility::where('id', $facility['id'])
                        ->where('venue_id', $venue->id)
                        ->update([
                            'name' => $facility['name'],
                            'icon' => $facility['icon'],
                        ]);
                } else {
                    // Create new facility
                    VenueFacility::create([
                        'venue_id' => $venue->id,
                        'name' => $facility['name'],
                        'icon' => $facility['icon'],
                    ]);
                }
            }
        } else {
            // Delete all facilities if none provided
            $venue->facilities()->delete();
        }

        // Update photos
        if (!empty($validated['photos'])) {
            // Get existing photo IDs
            $existingIds = collect($validated['photos'])
                ->pluck('id')
                ->filter()
                ->toArray();
            
            // Delete photos that are not in the update request
            $venue->photos()
                ->whereNotIn('id', $existingIds)
                ->delete();

            // Find the primary photo if any
            $primaryPhotoIndex = null;
            foreach ($validated['photos'] as $index => $photo) {
                if ($photo['is_primary'] ?? false) {
                    $primaryPhotoIndex = $index;
                    break;
                }
            }

            // Update or create photos
            foreach ($validated['photos'] as $index => $photo) {
                if (isset($photo['id']) && $photo['id']) {
                    // Update existing photo
                    VenuePhoto::where('id', $photo['id'])
                        ->where('venue_id', $venue->id)
                        ->update([
                            'file' => $photo['file'],
                            'is_primary' => $index === $primaryPhotoIndex,
                        ]);
                } else {
                    // Create new photo
                    VenuePhoto::create([
                        'venue_id' => $venue->id,
                        'file' => $photo['file'],
                        'is_primary' => $index === $primaryPhotoIndex,
                    ]);
                }
            }
        } else {
            // Delete all photos if none provided
            $venue->photos()->delete();
        }

        return response()->json(['message' => 'Venue updated successfully']);
    }
}
