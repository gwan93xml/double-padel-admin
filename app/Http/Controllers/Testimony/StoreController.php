<?php

namespace App\Http\Controllers\Testimony;

use App\Http\Controllers\Controller;
use App\Models\Testimony;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|string',
            'rating' => 'required|integer|between:1,5',
        ]);

        $testimony = Testimony::create($validated);
        return new JsonResource($testimony);
    }
}
