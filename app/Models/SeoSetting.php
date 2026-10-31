<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'indexing_enabled',
        'following_enabled',
        'site_name',
        'default_title',
        'default_description',
    ];

    protected function casts(): array
    {
        return [
            'indexing_enabled' => 'boolean',
            'following_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'indexing_enabled' => false,
                'following_enabled' => false,
                'site_name' => 'SillaGallery',
                'default_title' => 'SillaGallery | مبلمان و لوازم خانه',
                'default_description' => 'فروشگاه SillaGallery؛ انتخاب مبلمان و لوازم خانه با طراحی و کیفیت ماندگار.',
            ]
        );
    }

    public function robots(): string
    {
        return $this->robotsFor(true, true);
    }

    public function robotsFor(bool $pageIndex = true, bool $pageFollow = true): string
    {
        $index = $this->indexing_enabled && $pageIndex;
        $follow = $this->following_enabled && $pageFollow;

        return ($index ? 'index' : 'noindex') . ', ' . ($follow ? 'follow' : 'nofollow');
    }
}
