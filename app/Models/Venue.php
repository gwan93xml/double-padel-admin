<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
