<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Models\Review;

class DeleteController extends Controller
{
    public function __invoke(Review $review)
    {
        $review->delete();
        return response()->json(null, 204);
    }
}
