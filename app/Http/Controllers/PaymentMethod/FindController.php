<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(PaymentMethod $paymentMethod)
    {
        return new JsonResource($paymentMethod);
    }
}
