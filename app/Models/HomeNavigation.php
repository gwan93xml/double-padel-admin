<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeNavigation extends Model
{
    protected $fillable = [
        'icon',
        'small_icon',
        'name',
        'url',
    ];
}
