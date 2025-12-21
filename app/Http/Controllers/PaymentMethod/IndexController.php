<?php

namespace App\Http\Controllers\PaymentMethod;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function __invoke()
    {
        return inertia("PaymentMethod/Page");
    }
}
