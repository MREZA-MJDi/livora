<?php

namespace App\Services\Homepage;

use App\Models\HomeSection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeSectionProductResolver
{
    /**
     * Resolve only products that match the section's configured source.
     *
     * A titled section must never silently fall back to unrelated products.
     *
     * @param  array<int, int>  $excludedProductIds
     * @return Collection<int, Product>
     */
    public function resolve(HomeSection $section, array $excludedProductIds = []): Collection
    {
        $payload = $section->payload ?? [];
        $limit = $section->type === 'hero'
            ? 6
            : max(1, min(24, (int) ($payload['limit'] ?? 8)));

        $source = $section->type === 'hero'
            ? 'latest'
            : ($section->key === 'featured' ? 'featured' : ($payload['product_source'] ?? 'latest'));

        if ($source === 'selected') {
            $ids = array_values(array_unique(array_map('intval', $payload['product_ids'] ?? [])));

            if ($ids === []) {
                return collect();
            }
        }

        if ($source === 'category' && empty($payload['category_id'])) {
            return collect();
        }

        $query = $this->baseQuery($excludedProductIds);

        match ($source) {
            'featured' => $query->where('is_featured', true),
            'new' => $query->where('is_new', true),
            'installments' => $query->where('installment_enabled', true),
            'category' => $query->where('category_id', (int) $payload['category_id']),
            'selected' => $query->whereIn('products.id', $ids),
            default => $query->latest('created_at')->latest('id'),
        };

        if ($section->type === 'hero') {
            $query->where(function (Builder $imageQuery): void {
                $imageQuery
                    ->whereHas('images')
                    ->orWhereHas('variants.images');
            });
        }

        $products = $query
            ->limit($limit)
            ->get();

        if ($source === 'selected') {
            $products = $products
                ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
                ->values();
        }

        return $products;
    }

    private function baseQuery(array $excludedProductIds): Builder
    {
        return Product::query()
            ->active()
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true))
            ->when($excludedProductIds !== [], fn (Builder $query) => $query->whereNotIn('products.id', $excludedProductIds))
            ->with([
                'category',
                'images' => fn ($imageQuery) => $imageQuery
                    ->with('media')
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->limit(1),
                'variants.images.media',
            ])
            ->withCount([
                'allVariants',
                'variants as available_variants_count' => fn (Builder $variantQuery) => $variantQuery->where('stock', '>', 0),
            ]);
    }
}
