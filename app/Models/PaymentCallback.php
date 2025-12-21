<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCallback extends Model
{
    protected $fillable = [
        'payment_id',
        'transaction_id',
        'order_id',
        'status',
        'response',
        'signature_key',
        'is_verified',
    ];

    protected $casts = [
        'response' => 'array',
        'is_verified' => 'boolean',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
