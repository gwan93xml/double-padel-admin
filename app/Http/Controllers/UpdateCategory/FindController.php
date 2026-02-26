<?php

namespace App\Http\Controllers\UpdateCategory;

use App\Http\Controllers\Controller;
use App\Models\UpdateCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class FindController extends Controller
{
    public function __invoke(UpdateCategory $updateCategory)
    {
        return new JsonResource($updateCategory);
    }
}
