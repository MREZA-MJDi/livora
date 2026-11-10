<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->with([
                'media', 'cardMedia',
                'latestActiveProduct.images.media',
                'latestActiveProduct.variants.images.media',
            ])
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category, ShopFilterRequest $request): View
    {
        abort_unless($category->is_active, 404);

        $filters = $request->validated();
        $category->load([
            'media', 'cardMedia', 'bannerMedia',
            'children' => fn ($query) => $query->where('is_active', true)
                ->withCount(['products' => fn ($productQuery) => $productQuery->active()])
                ->with(['cardMedia', 'latestActiveProduct.images.media', 'latestActiveProduct.variants.images.media']),
            'latestActiveProduct.images.media',
            'latestActiveProduct.variants.images.media',
            'relatedCategories' => fn ($query) => $query
                ->where('is_active', true)
                ->withCount(['products' => fn ($productQuery) => $productQuery->active()])
                ->with(['latestActiveProduct.images.media', 'latestActiveProduct.variants.images.media']),
            'recommendedProducts' => fn ($query) => $query
                ->where('status', 'active')
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
                ->withCount(['allVariants', 'variants as available_variants_count' => fn ($variantQuery) => $variantQuery->where('stock', '>', 0)])
                ->with([
                    'category',
                    'images' => fn ($imageQuery) => $imageQuery
                        ->with('media')
                        ->orderByDesc('is_primary')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->limit(1),
                    'variants.images.media',
                ]),
        ]);

        $query = $category->products()
            ->withCount(['allVariants', 'variants as available_variants_count' => fn ($variantQuery) => $variantQuery->where('stock', '>', 0)])
            ->active()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            ->with([
                'category',
                'images' => fn ($imageQuery) => $imageQuery
                    ->with('media')
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->limit(1),
                'variants.images.media',
            ]);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($productQuery) use ($search): void {
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
            $query->where(function ($stockQuery): void {
                $stockQuery
                    ->where(function ($baseProductQuery): void {
                        $baseProductQuery->whereDoesntHave('allVariants')->where('stock', '>', 0);
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

        $products = $query->paginate(12)->withQueryString();

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
