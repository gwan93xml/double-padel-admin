<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELLED,
        self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'booking_number',
        'user_id',
        'court_schedule_id',
        'cart_id',
        'total_price',
        'status',
        'need_review',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courtSchedule()
    {
        return $this->belongsTo(CourtSchedule::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    
}
