<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class CompleteController extends Controller
{
    public function __invoke(Booking $booking)
    {
        $booking->markAsCompleted();
        $needReview = Review::where('user_id', $booking->user_id)
            ->where('venue_id', $booking->courtSchedule->court->venue_id)
            ->doesntExist();
        $booking->update(['need_review' => $needReview]);
        return new JsonResource($booking);
    }
}
