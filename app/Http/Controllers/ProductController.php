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
                'images' => fn ($imageQuery) => $imageQuery->limit(1),
                'variants.images',
            ])
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with([
                'category',
                'images',
            ])
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->latest()
            ->limit(4)
            ->get();

        return view('product.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
