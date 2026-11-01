<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SeoSetting extends Model
{
    protected $fillable = [
        'indexing_enabled', 'following_enabled', 'site_name', 'default_title',
        'default_description', 'default_og_image', 'twitter_handle',
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
                'indexing_enabled' => false,
                'following_enabled' => false,
                'site_name' => 'SillaGallery',
                'default_title' => 'SillaGallery | مبلمان و لوازم خانه',
                'default_description' => 'فروشگاه SillaGallery؛ انتخاب مبلمان و لوازم خانه با طراحی و کیفیت ماندگار.',
                'organization_name' => 'SillaGallery',
            ]
        ));
    }

    public function robots(): string
    {
        return $this->robotsFor(true, true);
    }

    public function robotsFor(bool $pageIndex = true, bool $pageFollow = true): string
    {
        // Public SEO is a two-key release gate: both site-wide switches must
        // be enabled before any page can be indexed or its links followed.
        $siteWideSeoEnabled = $this->indexing_enabled && $this->following_enabled;
        $index = $siteWideSeoEnabled && $pageIndex;
        $follow = $siteWideSeoEnabled && $pageFollow;
        return ($index ? 'index' : 'noindex') . ', ' . ($follow ? 'follow' : 'nofollow');
    }

    public function getDefaultOgImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->default_og_image);
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