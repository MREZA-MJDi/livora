<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\Product;
use App\Services\Installments\InstallmentPlanService;
use App\Services\Payments\PaymentService;
use App\Services\Payments\CheckoutPaymentViewData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Show checkout page.
     */
    public function index(): View
    {
        $cart = $this->getActiveCart();

        $cart->load([
            'items.product.category',
            'items.product.images',
            'items.variant',
        ]);

        abort_if(
            $cart->items->isEmpty(),
            404,
            'سبد خرید خالی است.'
        );

        $defaultAddress = Auth::user()
            ->defaultAddress()
            ->first();

        return view('checkout.index', [
            'cart' => $cart,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * @param Order $order
     * @return View
     */
    public function installment(
        Order $order
    ): View {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'installments',
        ]);

        /*
        |--------------------------------------------------------------------------
        | The installment plan must already exist.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $order->payment_method === 'installment'
            && $order->installment_enabled === true,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Make sure the order actually has installment rows.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $order->installments->isNotEmpty(),
            404
        );

        return view(
            'checkout.installment',
            [
                'order' => $order,
            ]
        );
    }
    /**
     * Create order from the active cart.
     */
    public function placeOrder(
        PlaceOrderRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        try {

            $cart = $this->getActiveCart();

            $cart->load([
                'items.product',
                'items.variant',
            ]);

            if ($cart->items->isEmpty()) {
                return redirect()
                    ->route('cart.index')
                    ->with(
                        'error',
                        'سبد خرید شما خالی است.'
                    );
            }

            $order = DB::transaction(function () use (
                $cart,
                $validated
            ) {
                // Serialize checkout against other checkout/cart operations.
                // A second request must never create a second order from the same cart.
                $lockedCart = Cart::query()
                    ->whereKey($cart->id)
                    ->where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if (! $lockedCart) {
                    throw new \RuntimeException(
                        'سبد خرید در حال پردازش یا قبلاً ثبت شده است؛ صفحه را تازه‌سازی کنید.'
                    );
                }

                $lockedCart->load([
                    'items.product',
                    'items.variant',
                ]);

                if ($lockedCart->items->isEmpty()) {
                    throw new \RuntimeException('سبد خرید شما خالی است.');
                }

                // Lock catalogue rows once in a stable order. This avoids per-cart-item
                // product/category/variant queries and keeps pricing and stock reads consistent.
                $cartItems = $lockedCart->items
                    ->sortBy(fn (CartItem $item) => sprintf('%020d:%020d', $item->product_id, $item->id))
                    ->values();
                $lockedCart->setRelation('items', $cartItems);

                $productIds = $cartItems->pluck('product_id')->filter()->unique()->sort()->values();

                $products = Product::query()
                    ->whereIn('id', $productIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $categoryIds = $products->pluck('category_id')->filter()->unique()->values();
                $activeCategoryIds = Category::query()
                    ->whereIn('id', $categoryIds)
                    ->where('is_active', true)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $variantsByProduct = ProductVariant::query()
                    ->whereIn('product_id', $productIds)
                    ->orderBy('product_id')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->groupBy('product_id');

                $allVariantsById = $variantsByProduct->flatten(1)->keyBy('id');
                $baseStockRequirements = [];
                $variantStockRequirements = [];
                $subtotalMinor = 0;

                foreach ($cartItems as $item) {
                    $product = $products->get($item->product_id);

                    if (
                        ! $product
                        || $product->status !== 'active'
                        || ! in_array((int) $product->category_id, $activeCategoryIds, true)
                    ) {
                        throw new \RuntimeException(
                            'یکی از محصولات سبد دیگر برای خرید فعال نیست؛ سبد را به‌روزرسانی کنید.'
                        );
                    }

                    if ((int) $item->quantity < 1) {
                        throw new \RuntimeException('تعداد یکی از محصولات سبد خرید معتبر نیست.');
                    }

                    $item->setRelation('product', $product);

                    $allProductVariants = $variantsByProduct->get($product->id, collect());
                    $selectedVariantIds = $item->selectedVariantIds();
                    $selectedIdLookup = array_fill_keys($selectedVariantIds, true);

                    $variants = $allProductVariants
                        ->filter(fn (ProductVariant $variant) => $variant->is_active && isset($selectedIdLookup[(int) $variant->id]))
                        ->sortBy('id')
                        ->values();

                    $allVariantTypes = $allProductVariants
                        ->pluck('type')
                        ->map(fn ($type) => (string) $type)
                        ->unique()
                        ->sort(SORT_STRING)
                        ->values()
                        ->all();

                    $selectedTypes = $variants
                        ->pluck('type')
                        ->map(fn ($type) => (string) $type)
                        ->unique()
                        ->sort(SORT_STRING)
                        ->values()
                        ->all();

                    if (
                        count($selectedVariantIds) !== $variants->count()
                        || $allVariantTypes !== $selectedTypes
                    ) {
                        throw new \RuntimeException(
                            "گزینه‌های محصول «{$product->name}» تغییر کرده‌اند؛ لطفاً محصول را از صفحه‌اش دوباره انتخاب کنید."
                        );
                    }

                    if ($variants->isEmpty()) {
                        $baseStockRequirements[$product->id] =
                            ($baseStockRequirements[$product->id] ?? 0) + (int) $item->quantity;
                    } else {
                        foreach ($variants as $variant) {
                            $variantStockRequirements[$variant->id] =
                                ($variantStockRequirements[$variant->id] ?? 0) + (int) $item->quantity;
                        }
                    }

                    $currentUnitPrice = max(
                        0,
                        (float) $product->price
                        + (float) $variants->sum(
                            fn (ProductVariant $variant) => (float) $variant->price_adjustment
                        )
                    );

                    // Do not silently charge a stale cart price.
                    if (round($currentUnitPrice, 2) !== round((float) $item->unit_price, 2)) {
                        throw new \RuntimeException(
                            "قیمت محصول «{$product->name}» تغییر کرده است؛ به سبد خرید برگردید و تعداد آن را دوباره تأیید کنید."
                        );
                    }

                    $subtotalMinor += (int) round($currentUnitPrice * (int) $item->quantity * 100);
                }

                foreach ($baseStockRequirements as $productId => $requiredQuantity) {
                    $product = $products->get($productId);

                    if (! $product || (int) $product->stock < $requiredQuantity) {
                        throw new \RuntimeException(
                            'موجودی محصول «' . ($product?->name ?? 'نامشخص') . '» برای تعداد درخواستی کافی نیست.'
                        );
                    }
                }

                foreach ($variantStockRequirements as $variantId => $requiredQuantity) {
                    $variant = $allVariantsById->get($variantId);

                    if (! $variant || ! $variant->is_active || (int) $variant->stock < $requiredQuantity) {
                        throw new \RuntimeException(
                            'موجودی یکی از گزینه‌های انتخاب‌شده کافی نیست؛ سبد خرید را به‌روزرسانی کنید.'
                        );
                    }
                }

                // Accumulate in minor units to avoid float drift before persisting DECIMAL(15,2).
                $subtotal = $subtotalMinor / 100;

                $shippingCost = 0;
                $discount = 0;

                $total =
                    $subtotal
                    + $shippingCost
                    - $discount;

                $order = Order::create([
                    'user_id' => Auth::id(),

                    'order_number' =>
                        $this->generateOrderNumber(),

                    'status' => 'pending',

                    'payment_status' => 'pending',

                    'payment_method' => 'online',

                    'payment_provider' => null,

                    'installment_enabled' => false,

                    'installment_cash_percent' => null,

                    'installment_cash_amount' => null,

                    'installment_deferred_amount' => null,

                    'installment_remainder_method' => null,

                    'installment_cheque_count' => null,

                    'installment_interval_months' => null,

                    'subtotal' => $subtotal,

                    'shipping_cost' => $shippingCost,

                    'discount' => $discount,

                    'total' => $total,

                    'first_name' =>
                        $validated['first_name'],

                    'last_name' =>
                        $validated['last_name'],

                    'phone' =>
                        $validated['phone'],

                    'email' =>
                        $validated['email'],

                    'province' =>
                        $validated['province'],

                    'city' =>
                        $validated['city'],

                    'address' =>
                        $validated['address'],

                    'postal_code' =>
                        $validated['postal_code'],

                    'unit' =>
                        $validated['unit'] ?? null,

                    'notes' =>
                        $validated['notes'] ?? null,
                ]);

                foreach ($lockedCart->items as $item) {

                    $order->items()->create([
                        'product_id' =>
                            $item->product_id,

                        /*
                         * Keep the existing single-variant relation for
                         * backwards compatibility; the full selection is
                         * preserved below in variant_options.
                         */
                        'product_variant_id' =>
                            $item->product_variant_id,

                        'variant_options' =>
                            $item->variant_options,

                        'product_name' =>
                            $item->product->name,

                        'sku' =>
                            $item->variant?->sku
                            ?? $item->product->sku,

                        'quantity' =>
                            $item->quantity,

                        'unit_price' =>
                            $item->unit_price,

                        'total' =>
                            (float) $item->unit_price
                            * (int) $item->quantity,
                    ]);
                }

                // Consume the cart in the same transaction as order creation.
                // This state transition is what makes repeated submissions idempotent.
                $lockedCart->items()->delete();
                $lockedCart->update(['status' => 'converted']);

                return $order;
            });

            return redirect()
                ->route(
                    'checkout.payment',
                    $order
                )
                ->with(
                    'success',
                    'سفارش شما با موفقیت ثبت شد.'
                );

        } catch (\RuntimeException $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );

        } catch (Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'ثبت سفارش انجام نشد. لطفاً دوباره تلاش کنید.'
                );
        }
    }

    /**
     * Show payment selection page.
     */
    public function payment(
        Order $order,
        CheckoutPaymentViewData $viewData
    ): View {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'latestPayment',
            'installments',
        ]);

        return view('checkout.payment', [
            'order' => $order,
            'preview' => false,
        ] + $viewData->forOrder($order));
    }

    /**
     * Start DigiPay / SnapPay / TorobPay installment payment.
     */
    public function startInstallmentPayment(
        Request $request,
        Order $order,
        PaymentService $paymentService
    ): RedirectResponse {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $validated = $request->validate([
            'gateway' => [
                'required',
                'string',
                'in:digipay,snappay,torobpay',
            ],
        ]);

        try {
            $result = $paymentService
                ->startInstallmentPayment(
                    $order,
                    $validated['gateway']
                );

            if (!($result['success'] ?? false)) {
                return back()->with(
                    'error',
                    $result['message']
                    ?? 'امکان شروع پرداخت وجود ندارد.'
                );
            }

            if (
                empty(
                $result['redirect_url']
                )
            ) {
                return back()->with(
                    'error',
                    'درگاه پرداخت لینک انتقال معتبری برنگرداند.'
                );
            }

            return redirect()->away(
                $result['redirect_url']
            );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'خطایی هنگام شروع پرداخت رخ داد.'
            );
        }
    }

    /**
     * Start Livora internal installment plan.
     *
     * Example:
     * 50% cash + remaining amount by cheque.
     */
    public function startInternalInstallment(
        Order $order,
        InstallmentPlanService $installmentPlanService
    ): RedirectResponse {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        try {
            $installmentPlanService->create($order);

            return redirect()
                ->route(
                    'checkout.installment',
                    $order
                )
                ->with(
                    'success',
                    'طرح خرید اقساطی برای سفارش ایجاد شد.'
                );

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                        ?: 'امکان ایجاد طرح اقساطی وجود ندارد.'
                );
        }
    }

    /**
     * Preview Livora installment plan.
     *
     * This endpoint will be useful later for
     * AJAX/calculator UI.
     */
    public function installmentPreview(
        Order $order,
        InstallmentPlanService $installmentPlanService
    ): RedirectResponse|array {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        try {
            return $installmentPlanService
                ->preview($order);
        } catch (Throwable $e) {
            report($e);

            return [
                'enabled' => false,
                'message' =>
                    $e->getMessage()
                        ?: 'طرح اقساطی قابل محاسبه نیست.',
            ];
        }
    }

    /**
     * Handle external payment callback.
     */
    public function paymentCallback(
        Request $request,
        string $gateway,
        PaymentService $paymentService
    ): RedirectResponse {
        abort_unless(
            in_array(
                $gateway,
                [
                    'digipay',
                    'snappay',
                    'torobpay',
                ],
                true
            ),
            404
        );

        try {
            $result = $paymentService
                ->handleCallback(
                    $gateway,
                    $request->all()
                );

            if (!($result['success'] ?? false)) {
                $orderId = $result['order_id'] ?? null;

                if (($result['data']['verification_pending'] ?? false) && $orderId) {
                    return redirect()
                        ->route('account.orders.show', $orderId)
                        ->with('warning', 'وضعیت تراکنش هنوز از درگاه تأیید نشده است؛ تا استعلام نهایی دوباره پرداخت نکنید.');
                }

                return redirect()
                    ->route('home')
                    ->with(
                        'error',
                        $result['message'] ?? 'پرداخت انجام نشد.'
                    );
            }

            $orderId =
                $result['order_id']
                ?? null;

            if ($orderId) {
                return redirect()
                    ->route(
                        'account.orders.show',
                        $orderId
                    )
                    ->with(
                        'success',
                        'پرداخت با موفقیت تأیید شد.'
                    );
            }

            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'پرداخت با موفقیت تأیید شد.'
                );
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'خطایی در تأیید پرداخت رخ داد.'
                );
        }
    }

    /**
     * Resolve all active variants represented by a cart item.
     *
     * The JSON snapshot keeps the historical selection even if a variant
     * is later deleted, while this lookup validates live availability.
     */
    protected function resolveCartItemVariants(
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
            ->lockForUpdate()
            ->get()
            ->sortBy(fn (ProductVariant $variant) => (int) $variant->id)
            ->values();
    }

    /**
     * Get all active option types as a sorted list.
     */
    protected function activeVariantTypes(Product $product): array
    {
        return $product->allVariants()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->map(fn ($type) => (string) $type)
            ->unique()
            ->sort(SORT_STRING)
            ->values()
            ->all();
    }

    /**
     * Get active customer cart.
     */
    protected function getActiveCart(): Cart
    {
        return Cart::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'status',
                'active'
            )
            ->firstOrCreate(
                [
                    'user_id' =>
                        Auth::id(),

                    'status' =>
                        'active',
                ],
                [
                    'session_id' =>
                        null,
                ]
            );
    }

    /**
     * Generate unique order number.
     */
    protected function generateOrderNumber(): string
    {
        do {
            $number =
                'LV-'
                . now()->format('ymd')
                . '-'
                . strtoupper(
                    Str::random(6)
                );
        } while (
            Order::query()
                ->where(
                    'order_number',
                    $number
                )
                ->exists()
        );

        return $number;
    }
}
