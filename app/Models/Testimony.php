<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimony extends Model
{
    protected $fillable = [
        'name',
        'title',
        'content',
        'image',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];
}
