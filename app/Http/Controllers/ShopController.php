<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShopBannerSetting;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index(ShopFilterRequest $request): View
    {
        $filters = $request->validated();

        $query = Product::query()
            ->with([
                'category',
                'images.media',
                'variants.images.media',
            ])
            ->withCount(['allVariants', 'variants as available_variants_count' => fn ($variantQuery) => $variantQuery->where('stock', '>', 0)])
            ->active()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));

        if (! empty($filters['category'])) {
            $query->whereHas('category', function ($categoryQuery) use ($filters) {
                $categoryQuery
                    ->where('slug', $filters['category'])
                    ->where('is_active', true);
            });
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('type', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if (($filters['min_price'] ?? null) !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (($filters['max_price'] ?? null) !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if ($filters['installment'] ?? false) {
            $query->where('installment_enabled', true);
        }

        if ($filters['featured'] ?? false) {
            $query->where('is_featured', true);
        }

        if ($filters['new'] ?? false) {
            $query->where('is_new', true);
        }

        if ($filters['in_stock'] ?? false) {
            // Variant-enabled products are available when at least one active
            // option has stock; base-product stock must not override option stock.
            $query->where(function ($stockQuery) {
                $stockQuery
                    ->where(function ($baseProductQuery) {
                        $baseProductQuery
                            ->whereDoesntHave('allVariants')
                            ->where('stock', '>', 0);
                    })
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('stock', '>', 0));
            });
        }

        // Keep available products ahead of unavailable ones for every sort mode.
        $query->orderByRaw("CASE WHEN EXISTS (
            SELECT 1 FROM product_variants
            WHERE product_variants.product_id = products.id
              AND product_variants.is_active = 1
              AND product_variants.stock > 0
        ) OR (
            NOT EXISTS (
                SELECT 1 FROM product_variants
                WHERE product_variants.product_id = products.id
            ) AND products.stock > 0
        ) THEN 0 ELSE 1 END");

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'name_asc' => $query->orderBy('name')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $products = $query
            ->paginate(8)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->withCount([
                'products' => fn ($productQuery) => $productQuery->active(),
            ])
            ->orderBy('sort_order')
            ->get();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'shopBanner' => ShopBannerSetting::current(),
        ]);
    }
}
