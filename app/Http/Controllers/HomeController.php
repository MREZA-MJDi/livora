<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomepageHeroItem;
use App\Models\HomepageHeroSetting;
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

        $heroSetting = HomepageHeroSetting::query()->first();

        $heroItems = HomepageHeroItem::query()
            ->whereNotNull('product_id')
            ->whereHas('product', fn ($query) => $query
                ->active()
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            )
            ->with([
                'product.category',
                'product.images' => fn ($imageQuery) => $imageQuery->limit(1),
            ])
            ->orderBy('slot')
            ->get()
            ->filter(fn (HomepageHeroItem $item) => filled($item->image_url))
            ->values();

        return view('home.index', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'newProducts' => $newProducts,
            'heroBackgroundUrl' => $heroSetting?->background_url,
            'heroItems' => $heroItems,
        ]);
    }
}
