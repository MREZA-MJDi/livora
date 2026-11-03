<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaLibrary
{
    public static function store(UploadedFile $file, int $userId, string $directory, ?string $alt = null): Media
    {
        $path = $file->store($directory, 'public');
        if (! $path) {
            throw new RuntimeException('فایل در کتابخانه رسانه ذخیره نشد.');
        }

        try {
            return Media::create([
                'user_id' => $userId,
                'disk' => 'public',
                'path' => $path,
                'filename' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'extension' => strtolower($file->getClientOriginalExtension()),
                'size' => $file->getSize(),
                'alt' => $alt,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }
    }

    public static function image(int|string|null $id): ?Media
    {
        if (! $id) {
            return null;
        }

        return Media::query()->whereKey($id)->where('mime_type', 'like', 'image/%')->firstOrFail();
    }
}
