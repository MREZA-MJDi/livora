<?php

namespace App\Models\Concerns;

use App\Models\SlugRedirect;
use Illuminate\Support\Facades\Cache;

trait TracksSlugRedirects
{
    protected static function bootTracksSlugRedirects(): void
    {
        static::updated(function ($model): void {
            if (! $model->wasChanged('slug')) {
                return;
            }

            $oldSlug = (string) $model->getOriginal('slug');
            $newSlug = (string) $model->slug;
            if ($oldSlug === '' || $newSlug === '' || $oldSlug === $newSlug) {
                return;
            }

            $prefix = $model->slugRedirectPrefix();
            $oldPath = '/'.trim($prefix.'/'.$oldSlug, '/');
            $newPath = '/'.trim($prefix.'/'.$newSlug, '/');

            if ($oldPath !== $newPath) {
                SlugRedirect::query()->updateOrCreate(
                    ['old_path' => $oldPath],
                    ['new_path' => $newPath, 'is_active' => true]
                );
                Cache::forget('slug_redirect:'.$oldPath);
            }
        });
    }

    protected function slugRedirectPrefix(): string
    {
        return '';
    }
}
