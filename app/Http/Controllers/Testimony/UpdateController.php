<?php

namespace App\Http\Controllers\Testimony;

use App\Http\Controllers\Controller;
use App\Models\Testimony;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(Request $request, Testimony $testimony)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'rating' => 'required|integer|between:1,5',
        ]);

        $testimony->update($validated);
        return new JsonResource($testimony);
    }
}
