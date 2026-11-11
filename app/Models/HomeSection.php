<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeSection extends Model
{
    protected $fillable = [
        'key', 'type', 'enabled', 'sort_order', 'eyebrow', 'title',
        'subtitle', 'cta_label', 'cta_url', 'image_path', 'payload',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'sort_order' => 'integer',
            'payload' => 'array',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        return self::publicImageUrl($this->image_path);
    }

    public static function publicImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }
}
