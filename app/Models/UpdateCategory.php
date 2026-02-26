<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function updates()
    {
        return $this->hasMany(Update::class, 'update_category_id');
    }
}
