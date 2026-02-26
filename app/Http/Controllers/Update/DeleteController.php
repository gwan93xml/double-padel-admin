<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Models\Update;

class DeleteController extends Controller
{
    public function __invoke(Update $update)
    {
        $update->delete();
        return response()->json(null, 204);
    }
}
