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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        $candidateCart = $this->getCart($request);

        return DB::transaction(function () use ($request, $product, $validated, $candidateCart) {
            $cart = Cart::query()
                ->whereKey($candidateCart->id)
                ->lockForUpdate()
                ->first();

            // Checkout may have converted this cart after the request began.
            // Start a fresh active cart rather than writing into a completed one.
            if (! $cart || $cart->status !== 'active') {
                $cart = $this->getCart($request);
                $cart = Cart::query()
                    ->whereKey($cart->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $product = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $product->status !== 'active'
                || ! $product->category()->where('is_active', true)->exists()
            ) {
                return back()->withErrors([
                    'product' => 'این محصول دیگر برای خرید فعال نیست.',
                ]);
            }

            $variants = $this->resolveSelectedVariants(
                $product,
                $validated['variants'] ?? null,
                $validated['product_variant_id'] ?? null
            );

            $requiredTypes = $product->allVariants()
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->map(fn ($type) => (string) $type)
                ->unique()
                ->sort(SORT_STRING)
                ->values()
                ->all();

            $selectedTypes = $variants->pluck('type')
                ->map(fn ($type) => (string) $type)
                ->unique()
                ->sort(SORT_STRING)
                ->values()
                ->all();

            if ($requiredTypes !== $selectedTypes) {
                return back()->withErrors([
                    'variants' => 'گزینه‌های محصول تغییر کرده‌اند؛ لطفاً محصول را دوباره انتخاب کنید.',
                ]);
            }

            $availableStock = $variants->isNotEmpty()
                ? (int) $variants->min('stock')
                : (int) $product->stock;

            $quantity = (int) $validated['quantity'];

            if ($quantity > $availableStock) {
                return back()->withErrors([
                    'quantity' => 'موجودی کافی نیست.',
                ]);
            }

            $unitPrice = max(
                0,
                (float) $product->price
                + (float) $variants->sum(
                    fn (ProductVariant $variant) => (float) $variant->price_adjustment
                )
            );

            $variantOptions = $variants
                ->sortBy('type', SORT_NATURAL | SORT_FLAG_CASE)
                ->map(fn (ProductVariant $variant) => [
                    'id' => (int) $variant->id,
                    'type' => (string) $variant->type,
                    'name' => (string) $variant->name,
                    'value' => (string) $variant->value,
                    'sku' => $variant->sku,
                    'color_hex' => $variant->color_hex,
                    'price_adjustment' => (float) $variant->price_adjustment,
                    'stock' => (int) $variant->stock,
                ])
                ->values()
                ->all();

            $variantKey = $this->buildVariantKey($variants, $product);
            $primaryVariantId = $variants->first()?->id;

            $itemQuery = $cart->items()
                ->where('product_id', $product->id)
                ->where(function ($query) use ($variantKey, $primaryVariantId, $variants) {
                    $query->where('variant_key', $variantKey);

                    if ($variants->count() === 1) {
                        $query->orWhere(function ($legacy) use ($primaryVariantId) {
                            $legacy
                                ->whereNull('variant_key')
                                ->whereNull('variant_options')
                                ->where('product_variant_id', $primaryVariantId);
                        });
                    }

                    if ($variants->isEmpty()) {
                        $query->orWhere(function ($legacy) {
                            $legacy
                                ->whereNull('variant_key')
                                ->whereNull('variant_options')
                                ->whereNull('product_variant_id');
                        });
                    }
                });

            $item = $itemQuery->lockForUpdate()->first();

            if ($item) {
                $quantity += (int) $item->quantity;
            }

            if ($quantity > 99) {
                return back()->withErrors([
                    'quantity' => 'تعداد یک محصول در سبد نمی‌تواند بیشتر از ۹۹ باشد.',
                ]);
            }

            if ($quantity > $availableStock) {
                return back()->withErrors([
                    'quantity' => 'موجودی کافی نیست.',
                ]);
            }

            $payload = [
                'product_id' => $product->id,
                'product_variant_id' => $primaryVariantId,
                'variant_key' => $variantKey,
                'variant_options' => $variantOptions ?: null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];

            if ($item) {
                $item->update($payload);
            } else {
                $cart->items()->create($payload);
            }

            $cart->setRelation('items', $cart->items()->get());

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
        });
    }

        public function update(
        UpdateCartRequest $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        return DB::transaction(function () use ($request, $item) {
            $cart = Cart::query()
                ->whereKey($item->cart_id)
                ->lockForUpdate()
                ->first();

            abort_unless($cart && $cart->status === 'active', 404);

            $lockedItem = CartItem::query()
                ->whereKey($item->id)
                ->where('cart_id', $cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureCartItemAccessible($request, $lockedItem);
            $validated = $request->validated();

            $lockedItem->load(['product', 'variant']);

            if (
                ! $lockedItem->product
                || $lockedItem->product->status !== 'active'
                || ! $lockedItem->product->category()->where('is_active', true)->exists()
            ) {
                return back()->withErrors([
                    'product' => 'این محصول دیگر برای خرید فعال نیست.',
                ]);
            }

            $variants = $this->resolveVariantsFromCartItem($lockedItem);
            $selectedVariantIds = $lockedItem->selectedVariantIds();

            $availableTypes = $lockedItem->product->allVariants()
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->map(fn ($type) => (string) $type)
                ->unique()
                ->sort(SORT_STRING)
                ->values()
                ->all();

            $selectedTypes = $variants->pluck('type')
                ->map(fn ($type) => (string) $type)
                ->unique()
                ->sort(SORT_STRING)
                ->values()
                ->all();

            if (
                count($selectedVariantIds) !== $variants->count()
                || $availableTypes !== $selectedTypes
            ) {
                return back()->withErrors([
                    'quantity' => 'گزینه‌های این محصول تغییر کرده‌اند؛ آن را از صفحه محصول دوباره انتخاب کنید.',
                ]);
            }

            $stock = $variants->isNotEmpty()
                ? (int) $variants->min('stock')
                : (int) $lockedItem->product->stock;

            if ((int) $validated['quantity'] > $stock) {
                return back()->withErrors([
                    'quantity' => 'موجودی کافی نیست.',
                ]);
            }

            $unitPrice = max(
                0,
                (float) $lockedItem->product->price
                + (float) $variants->sum(
                    fn (ProductVariant $variant) => (float) $variant->price_adjustment
                )
            );

            $lockedItem->update([
                'quantity' => (int) $validated['quantity'],
                'unit_price' => $unitPrice,
            ]);

            $cart->setRelation('items', $cart->items()->get());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'سبد به‌روزرسانی شد.',
                    'cart_count' => $cart->itemCount(),
                    'subtotal' => $cart->subtotal(),
                ]);
            }

            return back()->with('success', 'سبد خرید به‌روزرسانی شد.');
        });
    }

        public function remove(
        Request $request,
        CartItem $item
    ): RedirectResponse|JsonResponse {
        return DB::transaction(function () use ($request, $item) {
            $cart = Cart::query()
                ->whereKey($item->cart_id)
                ->lockForUpdate()
                ->first();

            abort_unless($cart && $cart->status === 'active', 404);

            $lockedItem = CartItem::query()
                ->whereKey($item->id)
                ->where('cart_id', $cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureCartItemAccessible($request, $lockedItem);
            $lockedItem->delete();

            $cart->setRelation('items', $cart->items()->get());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'محصول از سبد حذف شد.',
                    'cart_count' => $cart->itemCount(),
                    'subtotal' => $cart->subtotal(),
                ]);
            }

            return back()->with('success', 'محصول از سبد حذف شد.');
        });
    }

        public function clear(
        Request $request
    ): RedirectResponse|JsonResponse {
        $candidateCart = $this->getCart($request);

        return DB::transaction(function () use ($request, $candidateCart) {
            $cart = Cart::query()
                ->whereKey($candidateCart->id)
                ->lockForUpdate()
                ->first();

            if (! $cart || $cart->status !== 'active') {
                $cart = $this->getCart($request);
                $cart = Cart::query()
                    ->whereKey($cart->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $cart->items()->delete();
            $cart->setRelation('items', collect());

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'سبد خرید خالی شد.',
                    'cart_count' => 0,
                    'subtotal' => 0,
                ]);
            }

            return back()->with('success', 'سبد خرید خالی شد.');
        });
    }

        protected function resolveSelectedVariants(
        Product $product,
        ?array $submittedVariants,
        ?int $legacyVariantId
    ) {
        $submitted = collect($submittedVariants ?? [])
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if (
            $submitted->isEmpty()
            && $legacyVariantId
        ) {
            $submitted = collect([(int) $legacyVariantId]);
        }

        if ($submitted->isEmpty()) {
            return collect();
        }

        return $product->variants()
            ->whereIn('id', $submitted->unique()->all())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->sortBy(function (ProductVariant $variant) use ($submitted) {
                return $submitted->search(
                    fn ($id) => (int) $id === (int) $variant->id
                );
            })
            ->values();
    }

    protected function resolveVariantsFromCartItem(
        CartItem $item
    ) {
        $ids = $item->selectedVariantIds();

        if (empty($ids)) {
            return collect();
        }

        return ProductVariant::query()
            ->where('product_id', $item->product_id)
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    protected function buildVariantKey(
        $variants,
        Product $product
    ): string {
        if ($variants->isEmpty()) {
            return 'base';
        }

        $parts = $variants
            ->sortBy('type', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(
                fn (ProductVariant $variant) =>
                    Str::lower((string) $variant->type)
                    . ':'
                    . $variant->id
            )
            ->values()
            ->all();

        return hash(
            'sha256',
            $product->id . '|' . implode('|', $parts)
        );
    }

    /**
     * A cart item is mutable only by its authenticated customer owner or
     * by the browser session that created an active guest cart.
     */
    protected function ensureCartItemAccessible(Request $request, CartItem $item): void
    {
        $cart = $item->cart()->first();

        abort_unless($cart && $cart->status === 'active', 404);

        if (
            Auth::check()
            && Auth::user()->isCustomer()
            && (int) $cart->user_id === (int) Auth::id()
        ) {
            return;
        }

        $sessionId = (string) $request->session()->getId();

        abort_unless(
            $cart->user_id === null
                && filled($cart->session_id)
                && hash_equals((string) $cart->session_id, $sessionId),
            403
        );
    }

    protected function getCart(Request $request): Cart
    {
        if (Auth::check() && Auth::user()->isCustomer()) {
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
