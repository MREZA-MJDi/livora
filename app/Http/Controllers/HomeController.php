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
            ->with('latestActiveProduct.images')
            ->orderBy('sort_order')
            ->get();

        /*
         * The homepage is kept in sync with admin CRUD by ordering active
         * products by updated_at. Creating or editing a product therefore
         * moves it into the homepage collection without requiring flags.
         */
        $featuredProducts = Product::query()
            ->active()
            ->with(['category', 'images'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $newProducts = Product::query()
            ->active()
            ->new()
            ->with(['category', 'images'])
            ->latest()
            ->limit(8)
            ->get();

        $installmentProductCount = Product::query()
            ->active()
            ->where('installment_enabled', true)
            ->count();

        return view('home.index', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newProducts' => $newProducts,
            'installmentProductCount' => $installmentProductCount,
        ]);
    }
}
