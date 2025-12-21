<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name',
        'logo',
        'app_title',
        'company_name',
        'favicon',
        'address',
    ];

}
