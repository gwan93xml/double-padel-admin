<?php

namespace App\Http\Controllers\Testimony;

use App\Http\Controllers\Controller;
use App\Models\Testimony;

class DeleteController extends Controller
{
    public function __invoke(Testimony $testimony)
    {
        $testimony->delete();
        return response()->json(null, 204);
    }
}
