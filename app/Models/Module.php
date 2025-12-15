<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    public function actions()
    {
        return $this->belongsToMany(Action::class, 'module_action');
    }
}
