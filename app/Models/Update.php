<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    protected $fillable = [
        'update_category_id',
        'image',
        'title',
        'slug',
        'body',
    ];

    public function category()
    {
        return $this->belongsTo(UpdateCategory::class, 'update_category_id');
    }
}
