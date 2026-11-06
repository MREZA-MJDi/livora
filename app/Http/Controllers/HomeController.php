<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $sections = HomeSection::query()
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $finalCta = $sections->firstWhere('key', 'final-cta');
        $sections = $sections->reject(fn (HomeSection $section) => $section->key === 'final-cta')->values();

        $usedProductIds = [];

        foreach ($sections as $section) {
            $payload = $section->payload ?? [];
            $limit = match ($section->type) {
                'hero' => 6,
                'categories' => 4,
                default => max(1, min(24, (int) ($payload['limit'] ?? 8))),
            };

            if (in_array($section->type, ['hero', 'products'], true)) {
                $query = Product::query()
                    ->active()
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
                    ->with(['category', 'images' => fn ($imageQuery) => $imageQuery->with('media')->limit(1)])
                    ->when($section->type === 'hero', fn ($productQuery) => $productQuery->whereHas('images'))
                    ->withCount('allVariants')
                    ->whereNotIn('products.id', $usedProductIds);

                $source = $section->type === 'hero' ? 'latest' : ($section->key === 'featured' ? 'featured' : ($payload['product_source'] ?? 'latest'));
                if ($source === 'featured') {
                    $products = (clone $query)->featured()->latest('created_at')->limit($limit)->get();
                    if ($products->isEmpty()) {
                        $products = $query->latest('created_at')->limit($limit)->get();
                    }
                } elseif ($source === 'selected') {
                    $ids = array_values(array_unique(array_map('intval', $payload['product_ids'] ?? [])));
                    $products = $ids === []
                        ? collect()
                        : $query->whereIn('products.id', $ids)->limit($limit)->get()
                            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))->values();
                } else {
                    if ($source === 'new') {
                        $newProducts = (clone $query)->new()->latest('created_at')->limit($limit)->get();
                        $products = $newProducts->isNotEmpty()
                            ? $newProducts
                            : $query->latest('created_at')->limit($limit)->get();
                        $section->setRelation('products', $products);
                        $usedProductIds = array_merge($usedProductIds, $products->modelKeys());
                        continue;
                    } elseif ($source === 'installments') {
                        $query->where('installment_enabled', true)->latest();
                    } elseif ($source === 'category' && ! empty($payload['category_id'])) {
                        $query->where('category_id', (int) $payload['category_id'])->latest();
                    } else {
                        $query->latest('created_at');
                    }
                    $products = $query->limit($limit)->get();
                }

                $section->setRelation('products', $products);
                $usedProductIds = array_merge($usedProductIds, $products->modelKeys());
            }

            if ($section->type === 'categories') {
                $categories = Category::query()
                    ->active()
                    ->withCount(['products' => fn ($productQuery) => $productQuery->active()])
                    ->with(['media', 'cardMedia', 'latestActiveProduct.images' => fn ($imageQuery) => $imageQuery->with('media')->limit(1)])
                    ->orderByDesc('products_count')
                    ->orderBy('name')
                    ->limit($limit)
                    ->get();
                $section->setRelation('categories', $categories);
            }
        }

         $blogPosts = BlogPost::query()
            ->published()
            ->with('media')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('home.index', [
            'homeSections' => $sections,
            'blogPosts' => $blogPosts,
            'finalCta' => $finalCta,
        ]);
    }
}
