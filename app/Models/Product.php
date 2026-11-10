<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\TracksSlugRedirects;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TracksSlugRedirects;

    protected function slugRedirectPrefix(): string { return 'product'; }

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_at_price',
        'stock',
        'status',
        'is_featured',
        'is_new',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'content_sections',
        'key_features',
        'specifications',
        'care_guide',
        'faq_items',
        'canonical_url',
        'og_image',
        'banner_image',
        'banner_media_id',
        'banner_image_alt',
        'seo_index',
        'seo_follow',

        /*
         * Internal installment settings
         */
        'installment_enabled',
        'installment_cash_percent',
        'installment_remainder_method',
        'installment_cheque_count',
        'installment_interval_months',
    ];

    /**
     * @return HasMany
     */
    public function wishlists()
    {
        return $this->hasMany(
            Wishlist::class
        );
    }
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock' => 'integer',

            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'seo_index' => 'boolean',
            'seo_follow' => 'boolean',
            'content_sections' => 'array',
            'key_features' => 'array',
            'specifications' => 'array',
            'faq_items' => 'array',

            /*
             * Internal installment settings
             */
            'installment_enabled' => 'boolean',
            'installment_cash_percent' => 'integer',
            'installment_cheque_count' => 'integer',
            'installment_interval_months' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bannerMedia()
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->where('is_variant_only', false)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'product_related_products',
            'product_id',
            'related_product_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('is_active', true);
    }

    /**
     * All product variants, including inactive admin-managed options.
     */
    public function allVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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

    public function primaryImage(): ?ProductImage
    {
        return $this->images()
                ->where('is_primary', true)
                ->first()
            ?? $this->images()->first();
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (
            $this->compare_at_price === null ||
            (float) $this->compare_at_price <= (float) $this->price ||
            (float) $this->compare_at_price <= 0
        ) {
            return null;
        }

        return (int) round(
            (
                (float) $this->compare_at_price
                - (float) $this->price
            )
            / (float) $this->compare_at_price
            * 100
        );
    }

    public function getDimensionsAttribute(): ?string
    {
        return collect($this->specifications ?? [])
            ->firstWhere('label', 'ابعاد')['value'] ?? null;
    }

    public function getMaterialAttribute(): ?string
    {
        return collect($this->specifications ?? [])
            ->firstWhere('label', 'جنس')['value'] ?? null;
    }

    public function getPersianPriceAttribute(): string
    {
        return \App\Helpers\JalaliHelper::toPersianDigits(
            number_format((float) $this->price)
        );
    }

    public function getApproximateInstallmentAmountAttribute(): ?int
    {
        $cashPercent = (int) $this->installment_cash_percent;
        $chequeCount = (int) $this->installment_cheque_count;

        if (! $this->installment_enabled || $cashPercent < 1 || $cashPercent >= 100 || $chequeCount < 1) {
            return null;
        }

        return (int) round(
            ((float) $this->price * (100 - $cashPercent) / 100) / $chequeCount
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query
            ->where('status', 'active')
            ->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query
            ->where('status', 'active')
            ->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
