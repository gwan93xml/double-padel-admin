<?php

namespace App\Http\Controllers\Testimony;

use App\Http\Controllers\Controller;
use App\Models\Testimony;

class EditController extends Controller
{
    public function __invoke(Testimony $testimony)
    {
        return inertia('Testimony/Edit', [
            'testimony' => $testimony
        ]);
    }
}
