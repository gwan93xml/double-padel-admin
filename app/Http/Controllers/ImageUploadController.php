<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:5120', // 5MB
                'dimensions:min_width=100,min_height=100',
            ],
        ]);

        try {
            $image = $request->file('image');
            
            // Generate unique filename
            $filename = time() . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Store image in storage/app/public/images
            $path = $image->storeAs('images', $filename, 'public');
            
            // Get the full URL
            $url = Storage::disk('public')->url($path);
            
            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diunggah',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'filename' => $filename,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah gambar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
