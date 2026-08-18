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
