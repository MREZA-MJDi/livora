<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'image_alt',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'content_sections',
        'comparison_rows',
        'faq_items',
        'canonical_url',
        'og_image',
        'is_active',
        'sort_order',
        'seo_index',
        'seo_follow',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'seo_index' => 'boolean',
            'seo_follow' => 'boolean',
            'content_sections' => 'array',
            'comparison_rows' => 'array',
            'faq_items' => 'array',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (Str::startsWith($this->image, [
            'http://',
            'https://',
            '//',
        ])) {
            return $this->image;
        }

        $path = ltrim($this->image, '/');

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

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

    /**
     * Latest active product used as the homepage image fallback.
     *
     * Admin-uploaded category images keep priority. Seeded/external
     * category images fall back to the latest active product image.
     */
    public function latestActiveProduct(): HasOne
    {
        return $this->hasOne(Product::class)
            ->where('status', 'active')
            ->latestOfMany('updated_at');
    }

    public function getHomepageImageUrlAttribute(): ?string
    {
        $categoryImageIsUploaded =
            $this->image
            && ! Str::startsWith($this->image, [
                'http://',
                'https://',
                '//',
            ]);

        if ($categoryImageIsUploaded && $this->image_url) {
            return $this->image_url;
        }

        $productImage = $this->latestActiveProduct?->images?->first()?->url;

        return $productImage ?: $this->image_url;
    }

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
