<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\TracksSlugRedirects;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;
    use TracksSlugRedirects;

    protected function slugRedirectPrefix(): string { return 'categories'; }

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'image', 'media_id',
        'card_image', 'card_media_id', 'banner_image', 'banner_media_id',
        'banner_title', 'banner_subtitle', 'image_alt', 'meta_title',
        'meta_description', 'focus_keyword', 'content_sections', 'comparison_rows',
        'faq_items', 'canonical_url', 'og_image', 'is_active', 'sort_order',
        'seo_index', 'seo_follow',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer',
            'seo_index' => 'boolean', 'seo_follow' => 'boolean',
            'content_sections' => 'array', 'comparison_rows' => 'array', 'faq_items' => 'array',
        ];
    }

    public function media(): BelongsTo { return $this->belongsTo(Media::class); }
    public function cardMedia(): BelongsTo { return $this->belongsTo(Media::class, 'card_media_id'); }
    public function bannerMedia(): BelongsTo { return $this->belongsTo(Media::class, 'banner_media_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order'); }
    public function products(): HasMany { return $this->hasMany(Product::class); }

    public function relatedCategories(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'category_related_categories', 'category_id', 'related_category_id')
            ->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function recommendedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_recommended_products', 'category_id', 'product_id')
            ->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function latestActiveProduct(): HasOne
    {
        return $this->hasOne(Product::class)->where('status', 'active')->latestOfMany('updated_at');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->storedImageUrl($this->media, $this->image);
    }

    public function getCardImageUrlAttribute(): ?string
    {
        return $this->storedImageUrl($this->cardMedia, $this->card_image) ?: $this->image_url;
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        return $this->storedImageUrl($this->bannerMedia, $this->banner_image) ?: $this->card_image_url;
    }

    public function getHomepageImageUrlAttribute(): ?string
    {
        return $this->card_image_url ?: $this->latestActiveProduct?->images?->first()?->url;
    }

    private function storedImageUrl(?Media $media, ?string $path): ?string
    {
        if ($media) {
            if (Str::startsWith($media->path, ['http://', 'https://', '//'])) return $media->url;
            $disk = Storage::disk($media->disk ?: 'public');
            if ($disk->exists(ltrim($media->path, '/'))) return $media->url;
        }

        if (! $path) return null;
        if (Str::startsWith($path, ['http://', 'https://', '//'])) return $path;
        $path = ltrim($path, '/');

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->url($path) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
