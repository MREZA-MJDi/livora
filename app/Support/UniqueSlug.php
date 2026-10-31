<?php

namespace App\Support;

use Illuminate\Support\Str;

final class UniqueSlug
{
    public static function make(string $value, string $table, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($value));
        if ($base === '') {
            $base = 'content-' . Str::lower(Str::random(8));
        }

        $slug = $base;
        $suffix = 2;
        while (static::exists($slug, $table, $ignoreId)) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private static function exists(string $slug, string $table, ?int $ignoreId): bool
    {
        $query = match ($table) {
            'content_pages' => \App\Models\ContentPage::query(),
            'blog_posts' => \App\Models\BlogPost::query(),
            'blog_tags' => \App\Models\BlogTag::query(),
            default => throw new \InvalidArgumentException('Unsupported slug table.'),
        };

        return $query->where('slug', $slug)
            ->when($ignoreId, fn ($builder) => $builder->where('id', '!=', $ignoreId))
            ->exists();
    }
}