<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SeoSetting extends Model
{
    protected $fillable = [
        'indexing_enabled', 'following_enabled', 'site_name', 'default_title',
        'default_description', 'default_og_image', 'default_og_media_id', 'twitter_handle',
        'google_site_verification', 'bing_site_verification', 'organization_name',
        'organization_description', 'organization_logo', 'contact_email',
        'contact_phone', 'social_links',
    ];

    protected function casts(): array
    {
        return [
            'indexing_enabled' => 'boolean',
            'following_enabled' => 'boolean',
            'social_links' => 'array',
        ];
    }

    public static function current(): self
    {
        return Cache::rememberForever('seo_settings.current', static fn (): self => static::query()->firstOrCreate(
            ['id' => 1],
            [
                'indexing_enabled' => true,
                'following_enabled' => true,
                'site_name' => 'SillaGallery',
                'default_title' => 'مبلمان و دکوراسیون خانه | سیلا گالری | SillaGallery',
                'default_description' => 'سیلا گالری | SillaGallery؛ مشاهده مبلمان، میز و صندلی، محصولات اتاق خواب و راهنمای انتخاب و چیدمان خانه.',
                'organization_name' => 'SillaGallery',
            ]
        ));
    }

    public function defaultOgMedia()
    {
        return $this->belongsTo(Media::class, 'default_og_media_id');
    }

    public function robots(): string
    {
        return $this->robotsFor(true, true);
    }

    public function robotsFor(bool $pageIndex = true, bool $pageFollow = true): string
    {
        return 'index, follow';
    }

    public function getDefaultOgImageUrlAttribute(): ?string
    {
        return $this->defaultOgMedia?->url ?: $this->imageUrl($this->default_og_image);
    }

    public function getOrganizationLogoUrlAttribute(): ?string
    {
        return $this->imageUrl($this->organization_logo);
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return Storage::disk('public')->url($path);
    }
}