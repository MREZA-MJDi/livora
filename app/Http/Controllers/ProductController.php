<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::query()
            ->with([
                'category',
                'images',
                'variants.images',
                'relatedProducts' => fn ($relatedQuery) => $relatedQuery
                    ->where('status', 'active')
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
                    ->with(['category', 'images' => fn ($imageQuery) => $imageQuery->limit(1)]),
            ])
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = $product->relatedProducts
            ->where('id', '!=', $product->id)
            ->take(4)
            ->values();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::query()
                ->active()
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->with([
                    'category',
                    'images' => fn ($imageQuery) => $imageQuery->limit(1),
                ])
                ->where('id', '!=', $product->id)
                ->where('category_id', $product->category_id)
                ->latest()
                ->limit(4)
                ->get();
        }

        return view('product.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
