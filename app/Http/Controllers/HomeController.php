<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->with(['category', 'images'])
            ->latest()
            ->limit(8)
            ->get();

        /*
         * Featured products stay first. Any remaining homepage slots are
         * filled with the latest active products so a product created or
         * activated from admin becomes visible without requiring a separate
         * "featured" or "new" flag.
         */
        if ($featuredProducts->count() < 8) {
            $fallbackProducts = Product::query()
                ->active()
                ->whereNotIn('id', $featuredProducts->pluck('id'))
                ->with(['category', 'images'])
                ->latest()
                ->limit(8 - $featuredProducts->count())
                ->get();

            $featuredProducts = $featuredProducts
                ->concat($fallbackProducts)
                ->values();
        }

        $newProducts = Product::query()
            ->active()
            ->new()
            ->with(['category', 'images'])
            ->latest()
            ->limit(8)
            ->get();

        return view('home.index', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newProducts' => $newProducts,
        ]);
    }
}
