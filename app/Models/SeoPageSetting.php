<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPageSetting extends Model
{
    protected $fillable = [
        'route_key', 'path', 'label', 'meta_title', 'meta_description',
        'canonical_url', 'og_image', 'seo_index', 'seo_follow',
    ];

    protected function casts(): array
    {
        return [
            'seo_index' => 'boolean',
            'seo_follow' => 'boolean',
        ];
    }

    public static function forRoute(?string $routeName): ?self
    {
        if (! $routeName) {
            return null;
        }

        $key = match (true) {
            $routeName === 'home' => 'home',
            $routeName === 'shop.index' => 'shop',
            $routeName === 'categories.index' => 'categories',
            $routeName === 'blog.index' => 'blog',
            $routeName === 'contact' => 'contact',
            default => null,
        };

        return $key ? static::query()->where('route_key', $key)->first() : null;
    }

    public function robots(): string
    {
        $settings = SeoSetting::current();

        return $settings->robotsFor($this->seo_index, $this->seo_follow);
    }
}