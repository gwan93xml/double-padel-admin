<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['group', 'code', 'name', 'image', 'transaction_fee', 'how_to_pay'];

    protected $casts = [
        'transaction_fee' => 'integer',
        'how_to_pay' => 'array',
    ];
}
