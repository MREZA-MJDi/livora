<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Support\\Facades\\Storage;

class ShopBannerSetting extends Model
{
    protected $table = 'shop_page_settings';

    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'banner_image',
        'banner_image_alt',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'eyebrow' => 'SILLAGALLERY COLLECTION',
                'title' => 'برای خانه‌ای با امضای شما',
                'description' => 'مجموعه‌ای از مبلمان و عناصر خانه را ببینید، مقایسه کنید و انتخابی متناسب با فضای زندگی خود داشته باشید.',
                'is_active' => true,
            ]
        );
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        if (! $this->banner_image) {
            return null;
        }

        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        return Storage::disk('public')->url($this->banner_image);
    }
}
