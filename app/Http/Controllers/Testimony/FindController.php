<?php

namespace App\Http\Controllers\Testimony;

use App\Http\Controllers\Controller;
use App\Models\Testimony;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(Testimony $testimony)
    {
        return new JsonResource($testimony);
    }
}
