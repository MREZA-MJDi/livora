<?php

namespace App\Services\Homepage;

use App\Models\HomeSection;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeSectionProductResolver
{
    /**
     * Resolve only products that match the section's configured source.
     *
     * A titled section must never silently fall back to unrelated products.
     *
     * @return Collection<int, Product>
     */
    public function resolve(HomeSection $section): Collection
    {
        $payload = $section->payload ?? [];
        $limit = $section->type === 'hero'
            ? 6
            : max(1, min(24, (int) ($payload['limit'] ?? 8)));

        $configuredSource = $payload['product_source'] ?? 'latest';
        $source = $section->type === 'hero'
            ? 'latest'
            : ($section->key === 'featured' && $configuredSource === 'latest' ? 'featured' : $configuredSource);

        if ($source === 'selected') {
            $ids = array_values(array_unique(array_map('intval', $payload['product_ids'] ?? [])));

            if ($ids === []) {
                return collect();
            }
        }

        if ($source === 'category' && empty($payload['category_id'])) {
            return collect();
        }

        if ($source === 'popular') {
            $ids = OrderItem::query()
                ->whereNotNull('product_id')
                ->whereHas('order', fn (Builder $orderQuery) => $orderQuery->where('payment_status', 'paid'))
                ->whereHas('product', fn (Builder $productQuery) => $productQuery
                    ->active()
                    ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true)))
                ->select('product_id')
                ->groupBy('product_id')
                ->orderByRaw('SUM(quantity) DESC')
                ->orderBy('product_id')
                ->limit($limit)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($ids === []) {
                return collect();
            }
        }

        $query = $this->baseQuery();

        match ($source) {
            'featured' => $query->where('is_featured', true),
            'new' => $query->where('is_new', true),
            'popular' => $query->whereIn('products.id', $ids),
            'installments' => $query->where('installment_enabled', true),
            'category' => $query->where('category_id', (int) $payload['category_id']),
            'selected' => $query->whereIn('products.id', $ids),
            default => $query->latest('created_at')->latest('id'),
        };

        $products = $query
            ->limit($limit)
            ->get();

        if (in_array($source, ['selected', 'popular'], true)) {
            $products = $products
                ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))
                ->values();
        }

        return $products;
    }

    private function baseQuery(): Builder
    {
        return Product::query()
            ->active()
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('is_active', true))
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
