<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'variant_source_path',
        'filename',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'alt',
        'description',
        'thumbnail_path', 'card_path', 'detail_path',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * User who uploaded this media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full public URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://', '//'])) {
            return $this->path;
        }

        return Storage::disk($this->disk ?: 'public')
            ->url(ltrim($this->path, '/'));
    }

    public function getThumbnailUrlAttribute(): string { return $this->variantUrl($this->thumbnail_path); }
    public function getCardUrlAttribute(): string { return $this->variantUrl($this->card_path); }
    public function getDetailUrlAttribute(): string { return $this->variantUrl($this->detail_path); }

    private function variantUrl(?string $path): string
    {
        if (! $path) return $this->url;
        if (Str::startsWith($path, ['http://', 'https://', '//'])) return $path;
        $disk = Storage::disk($this->disk ?: 'public');
        return $disk->exists(ltrim($path, '/')) ? $disk->url(ltrim($path, '/')) : $this->url;
    }

    /**
     * Human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1) . ' MB';
        }

        return number_format(
                $bytes / (1024 * 1024 * 1024),
                1
            ) . ' GB';
    }

    /**
     * Determine whether the media is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * @return HasMany
     */
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productBanners(): HasMany
    {
        return $this->hasMany(Product::class, 'banner_media_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function categoryCards(): HasMany
    {
        return $this->hasMany(Category::class, 'card_media_id');
    }

    public function categoryBanners(): HasMany
    {
        return $this->hasMany(Category::class, 'banner_media_id');
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function contentPages(): HasMany
    {
        return $this->hasMany(ContentPage::class);
    }

    public function shopBannerSettings(): HasMany
    {
        return $this->hasMany(ShopBannerSetting::class);
    }

    public function seoSettings(): HasMany
    {
        return $this->hasMany(SeoSetting::class, 'default_og_media_id');
    }
}
