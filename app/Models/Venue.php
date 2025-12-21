<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'province',
        'city',
        'address',
        'latitude',
        'longitude',
        'min_price',
        'max_price',
        'average_rating',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'min_price' => 'double',
        'max_price' => 'double',
        'average_rating' => 'double',
    ];

    public function facilities(): HasMany
    {
        return $this->hasMany(VenueFacility::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VenuePhoto::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function updatePriceRange(): void
    {
        $venueMaxPrice = CourtSchedule::whereHas('court', function ($query) {
            $query->where('venue_id', $this->id);
        })
            ->where('date', '>=', now())
            ->max('price');

        $venueMinPrice = CourtSchedule::whereHas('court', function ($query) {
            $query->where('venue_id', $this->id);
        })
            ->where('date', '>=', now())
            ->min('price');

        $this->update([
            'min_price' => $venueMinPrice ?? 0,
            'max_price' => $venueMaxPrice ?? 0,
        ]);
    }

}
