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
            ->with('latestActiveProduct.primaryImage')
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::query()
            ->with(['category', 'primaryImage'])
            ->featured()
            ->latest('updated_at')
            ->limit(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::query()
                ->with(['category', 'primaryImage'])
                ->active()
                ->latest('updated_at')
                ->limit(8)
                ->get();
        }

        $newProducts = Product::query()
            ->active()
            ->new()
            ->with(['category', 'images.media'])
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
