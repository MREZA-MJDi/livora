<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->getCart($request);

        $cart->load([
            'items.product.category',
            'items.product.images',
            'items.variant',
        ]);

        return view('cart.index', [
            'cart' => $cart,
        ]);
    }

    public function add(
        AddToCartRequest $request,
        Product $product
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();

        $cart = $this->getCart($request);

        $variant = null;

        if (! empty($validated['product_variant_id'])) {
            $variant = ProductVariant::query()
                ->whereKey($validated['product_variant_id'])
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $availableStock = $variant?->stock ?? $product->stock;

        $unitPrice = (float) $product->price;

        if ($variant) {
            $unitPrice += (float) $variant->price_adjustment;
        }

        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $quantity = (int) $validated['quantity'];

        if ($item) {
            $quantity += $item->quantity;
        }

        if ($quantity > $availableStock) {
            return back()->withErrors([
                'quantity' => 'موجودی کافی نیست.',
            ]);
        }

        if ($item) {
            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);
        }

        $cart->load('items');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'محصول به سبد اضافه شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function update(
        UpdateCartRequest $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        $this->authorize('update', $item);

        $validated = $request->validated();

        $item->load([
            'cart',
            'product',
            'variant',
        ]);

        $stock = $item->variant?->stock ?? $item->product->stock;

        if ((int) $validated['quantity'] > $stock) {
            return back()->withErrors([
                'quantity' => 'موجودی کافی نیست.',
            ]);
        }

        $item->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        $cart = $item->cart->fresh('items');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'سبد به‌روزرسانی شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return back()->with(
            'success',
            'سبد خرید به‌روزرسانی شد.'
        );
    }

    public function remove(
        Request $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        $this->authorize('delete', $item);

        $cart = $item->cart;

        $item->delete();

        $cart->load('items');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'محصول حذف شد.',
                'cart_count' => $cart->itemCount(),
                'subtotal' => $cart->subtotal(),
            ]);
        }

        return back()->with(
            'success',
            'محصول از سبد حذف شد.'
        );
    }

    public function clear(
        Request $request
    ): RedirectResponse|JsonResponse {
        $cart = $this->getCart($request);

        $cart->items()->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'سبد خرید خالی شد.',
                'cart_count' => 0,
                'subtotal' => 0,
            ]);
        }

        return back()->with(
            'success',
            'سبد خرید خالی شد.'
        );
    }

    protected function getCart(Request $request): Cart
    {
        if (Auth::check()) {
            return Cart::query()->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'active',
                ],
                [
                    'session_id' => null,
                ]
            );
        }

        $sessionId = $request->session()->getId();

        return Cart::query()->firstOrCreate(
            [
                'session_id' => $sessionId,
                'status' => 'active',
            ],
            [
                'user_id' => null,
            ]
        );
    }
}
