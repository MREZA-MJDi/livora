<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $path = $validated['image']->store('editor', 'public');
        if (! $path) {
            return response()->json(['message' => 'ذخیره تصویر انجام نشد.'], 500);
        }

        return response()->json([
            'url' => asset('storage/' . $path),
            'path' => $path,
        ], 201);
    }
}