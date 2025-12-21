<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'cart_id',
        'transaction_id',
        'midtrans_order_id',
        'amount',
        'status',
        'payment_method_id',
        'midtrans_response',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'midtrans_response' => 'array',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
