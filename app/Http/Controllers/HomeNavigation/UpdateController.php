<?php

namespace App\Http\Controllers\HomeNavigation;

use App\Http\Controllers\Controller;
use App\Models\HomeNavigation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateController extends Controller
{
    public function __invoke(Request $request, HomeNavigation $homeNavigation)
    {
        $validated = $request->validate([
            'icon' => 'required|string',
            'small_icon' => 'nullable|string',
            'name' => 'required|string',
            'url' => 'required|string',
        ]);

        $homeNavigation->update($validated);

        return new JsonResource($homeNavigation);
    }
}
