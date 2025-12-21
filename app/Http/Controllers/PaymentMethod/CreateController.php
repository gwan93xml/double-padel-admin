<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;

class CreateController extends Controller
{
    public function __invoke()
    {
        return inertia('PaymentMethod/Create', [
            'paymentMethod' => null,
        ]);
    }
}
