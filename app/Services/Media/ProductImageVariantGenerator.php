<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductImageVariantGenerator
{
    private const SIZES = [
        'thumbnail_path' => ['width' => 240, 'height' => 300, 'name' => 'thumbnail'],
        'card_path' => ['width' => 600, 'height' => 750, 'name' => 'card'],
        'detail_path' => ['width' => 1200, 'height' => 1500, 'name' => 'detail'],
    ];

    public function generate(Media $media): Media
    {
        if (! $media->is_image || ! $media->disk || str_starts_with($media->path, 'http')) {
            return $media;
        }

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw new RuntimeException('GD with WebP support is required to generate product image variants.');
        }

        $disk = Storage::disk($media->disk);
        $sourcePath = ltrim($media->path, '/');
        if (! $disk->exists($sourcePath)) {
            throw new RuntimeException('Original product image is missing: '.$sourcePath);
        }

        $source = @imagecreatefromstring($disk->get($sourcePath));
        if (! $source) {
            throw new RuntimeException('The original product image could not be decoded.');
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            if ($width < 400 || $height < 500) {
                throw new RuntimeException('Product image must be at least 400 × 500 pixels.');
            }

            foreach (self::SIZES as $column => $size) {
                $target = $this->cover($source, $width, $height, $size['width'], $size['height']);
                try {
                    ob_start();
                    $written = imagewebp($target, null, 84);
                    $contents = ob_get_clean();
                    if (! $written || ! is_string($contents) || $contents === '') {
                        throw new RuntimeException('Could not encode '.$size['name'].' product image variant.');
                    }

                    $path = 'products/variants/'.$media->id.'/'.$size['name'].'.webp';
                    $disk->put($path, $contents);
                    $media->{$column} = $path;
                } finally {
                    imagedestroy($target);
                }
            }

            $media->save();

            return $media->refresh();
        } finally {
            imagedestroy($source);
        }
    }

    private function cover($source, int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight)
    {
        $targetRatio = $targetWidth / $targetHeight;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $sourceX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $sourceY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $sourceX = 0;
            $sourceY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
        imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($target, $source, 0, 0, $sourceX, $sourceY, $targetWidth, $targetHeight, $cropWidth, $cropHeight);

        return $target;
    }
}
