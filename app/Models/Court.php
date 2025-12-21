<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'name',
        'court_type',
        'price_per_hour',
        'capacity',
        'status',
        'image',
    ];

    protected $casts = [
        'price_per_hour' => 'integer',
        'capacity' => 'integer',
    ];

    /**
     * Get the venue that owns the court.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
