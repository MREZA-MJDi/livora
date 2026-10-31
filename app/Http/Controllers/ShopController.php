<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index(ShopFilterRequest $request): View
    {
        $filters = $request->validated();

        $query = Product::query()
            ->with([
                'category',
                'images' => fn ($imageQuery) => $imageQuery->limit(1),
            ])
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
                    ->orWhere('description', 'like', "%{$search}%");
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

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),
            'name_asc' => $query->orderBy('name')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $products = $query
            ->paginate(12)
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
        ]);
    }
}
