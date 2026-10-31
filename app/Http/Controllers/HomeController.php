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
            ->with(['latestActiveProduct.images' => fn ($imageQuery) => $imageQuery->limit(1)])
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        /*
         * The homepage is kept in sync with admin CRUD by ordering active
         * products by updated_at. Creating or editing a product therefore
         * moves it into the homepage collection without requiring flags.
         */
        $featuredProducts = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with([
                'category',
                'images' => fn ($imageQuery) => $imageQuery->limit(1),
            ])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $newProducts = Product::query()
            ->active()
            ->new()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with([
                'category',
                'images' => fn ($imageQuery) => $imageQuery->limit(1),
            ])
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
