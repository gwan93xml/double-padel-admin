<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'court_schedule_id', 'price'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function courtSchedule()
    {
        return $this->belongsTo(CourtSchedule::class);
    }
}
