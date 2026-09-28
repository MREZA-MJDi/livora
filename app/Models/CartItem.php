<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'variant_key',
        'variant_options',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'variant_options' => 'array',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    /**
     * IDs of all selected variants, including the legacy single-variant field.
     */
    public function selectedVariantIds(): array
    {
        $options = is_array($this->variant_options)
            ? $this->variant_options
            : [];

        $ids = collect($options)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($ids) && $this->product_variant_id) {
            $ids = [(int) $this->product_variant_id];
        }

        return array_values(array_unique($ids));
    }

    public function total(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }
}
