<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ContentPage extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'body', 'featured_image',
        'featured_image_alt', 'meta_title', 'meta_description', 'canonical_url',
        'og_image', 'status', 'seo_index', 'seo_follow', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'seo_index' => 'boolean',
            'seo_follow' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->publicImageUrl($this->featured_image);
    }

    public function getOgImageUrlAttribute(): ?string
    {
        return $this->publicImageUrl($this->og_image ?: $this->featured_image);
    }

    private function publicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}