<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContentPage extends Model
{
    use \App\Models\Concerns\TracksSlugRedirects;

    protected function slugRedirectPrefix(): string { return ''; }

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'body', 'featured_image', 'media_id',
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

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $media = $this->relationLoaded('media') ? $this->getRelation('media') : null;
        if ($media) {
            if (str_starts_with($media->path, 'http://') || str_starts_with($media->path, 'https://')) {
                return $media->url;
            }

            $disk = Storage::disk($media->disk ?: 'public');
            if ($disk->exists(ltrim($media->path, '/'))) {
                return $media->url;
            }
        }

        return $this->publicImageUrl($this->featured_image);
    }

    public function getOgImageUrlAttribute(): ?string
    {
        if (! filled($this->og_image)) {
            return $this->featured_image_url;
        }

        return $this->publicImageUrl($this->og_image);
    }

    private function publicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'https://') || str_starts_with($path, 'http://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        return Storage::disk('public')->exists($path)
            ? Storage::disk('public')->url($path)
            : null;
    }
}
