<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $media = MediaLibrary::store(
            $validated['image'],
            (int) $request->user()->id,
            'editor',
            $validated['alt'] ?? null,
        );

        return response()->json([
            'id' => $media->id,
            'name' => $media->original_name,
            'alt' => $media->alt,
            'url' => $media->url,
            'path' => $media->path,
            'size' => $media->human_size,
        ], 201);
    }
}
