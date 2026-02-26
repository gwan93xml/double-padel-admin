<?php

namespace App\Http\Controllers\UpdateCategory;

use App\Http\Controllers\Controller;
use App\Models\UpdateCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UpdateController extends Controller
{
    public function __invoke(Request $request, UpdateCategory $updateCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:update_categories,name,' . $updateCategory->id,
        ]);

        $updateCategory->update([
            ...$validated,
            'slug' => Str::slug($validated['name']),
        ]);

        return new JsonResource($updateCategory);
    }
}
