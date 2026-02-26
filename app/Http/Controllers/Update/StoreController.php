<?php

namespace App\Http\Controllers\Update;

use App\Http\Controllers\Controller;
use App\Models\Update;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'update_category_id' => 'required|exists:update_categories,id',
            'title' => 'required|string|unique:updates',
            'body' => 'required|string',
            'image' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        $update = Update::create($validated);

        return new JsonResource($update);
    }
}
