<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;

class EditController extends Controller
{
    public function __invoke(PaymentMethod $paymentMethod)
    {
        return inertia('PaymentMethod/Edit', [
            'paymentMethod' => $paymentMethod,
        ]);
    }
}
