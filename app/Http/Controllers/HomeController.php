<?php

namespace App\Http\Controllers;

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

        $usedProductIds = [];

        foreach ($sections as $section) {
            $payload = $section->payload ?? [];
            $limit = max(1, min(24, (int) ($payload['limit'] ?? 8)));

            if (in_array($section->type, ['hero', 'products'], true)) {
                $query = Product::query()
                    ->active()
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
                    ->with(['category', 'images' => fn ($imageQuery) => $imageQuery->limit(1)])
                    ->withCount('allVariants')
                    ->whereNotIn('products.id', $usedProductIds);

                $source = $payload['product_source'] ?? 'latest';
                if ($source === 'selected') {
                    $ids = array_values(array_unique(array_map('intval', $payload['product_ids'] ?? [])));
                    $products = $ids === []
                        ? collect()
                        : $query->whereIn('products.id', $ids)->limit($limit)->get()
                            ->sortBy(fn (Product $product) => array_search($product->id, $ids, true))->values();
                } else {
                    if ($source === 'new') {
                        $query->new()->latest();
                    } elseif ($source === 'installments') {
                        $query->where('installment_enabled', true)->latest();
                    } elseif ($source === 'category' && ! empty($payload['category_id'])) {
                        $query->where('category_id', (int) $payload['category_id'])->latest();
                    } else {
                        $query->latest('updated_at');
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
                    ->with(['media', 'cardMedia', 'latestActiveProduct.images' => fn ($imageQuery) => $imageQuery->limit(1)])
                    ->limit($limit)
                    ->get();
                $section->setRelation('categories', $categories);
            }
        }

        return view('home.index', ['homeSections' => $sections]);
    }
}
