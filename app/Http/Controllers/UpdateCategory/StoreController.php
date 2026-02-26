<?php

namespace App\Http\Controllers\UpdateCategory;

use App\Http\Controllers\Controller;
use App\Models\UpdateCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:update_categories',
        ]);

        $updateCategory = UpdateCategory::create([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        return new JsonResource($updateCategory);
    }
}
