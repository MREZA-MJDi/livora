<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $wishlist = Auth::user()
            ->wishlists()
            ->whereHas('product', function ($query) {
                $query->active()
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true));
            })
            ->with([
                'product.category',
                'product.images',
            ])
            ->latest()
            ->paginate(12);

        return view('wishlist.index', [
            'wishlist' => $wishlist,
        ]);
    }

    public function store(Product $product): RedirectResponse
    {
        if (
            $product->status !== 'active'
            || ! $product->category()->where('is_active', true)->exists()
        ) {
            return back()->with(
                'error',
                'این محصول در حال حاضر در فروشگاه قابل دسترسی نیست.'
            );
        }

        Auth::user()
            ->wishlists()
            ->firstOrCreate([
                'product_id' => $product->id,
            ]);

        return back()->with(
            'success',
            'محصول به علاقه‌مندی‌ها اضافه شد.'
        );
    }

    public function destroy(Product $product): RedirectResponse
    {
        Auth::user()
            ->wishlists()
            ->where('product_id', $product->id)
            ->delete();

        return back()->with(
            'success',
            'محصول از علاقه‌مندی‌ها حذف شد.'
        );
    }
}
