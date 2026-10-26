<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\Product;
use App\Services\Installments\InstallmentPlanService;
use App\Services\Payments\PaymentService;
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

                $subtotal = 0;

                foreach ($cart->items as $item) {
                    // Re-read and lock the catalogue row inside the order transaction.
                    // A product/category may have been disabled since it entered the cart.
                    $product = Product::query()
                        ->whereKey($item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (
                        ! $product
                        || $product->status !== 'active'
                        || ! $product->category()->where('is_active', true)->exists()
                    ) {
                        throw new \RuntimeException(
                            'یکی از محصولات سبد دیگر برای خرید فعال نیست؛ سبد را به‌روزرسانی کنید.'
                        );
                    }

                    $item->setRelation('product', $product);
                    $variants = $this->resolveCartItemVariants($item);
                    $selectedVariantIds = $item->selectedVariantIds();

                    if (
                        count($selectedVariantIds) !== $variants->count()
                        || $this->activeVariantTypes($product) !== $variants->pluck('type')->map(fn ($type) => (string) $type)->unique()->sort()->values()->all()
                    ) {
                        throw new \RuntimeException(
                            "گزینه‌های محصول «{$product->name}» تغییر کرده‌اند؛ لطفاً محصول را از صفحه‌اش دوباره انتخاب کنید."
                        );
                    }

                    $stock = $variants->isNotEmpty()
                        ? (int) $variants->min('stock')
                        : (int) $product->stock;

                    if ($stock < 1 || $item->quantity > $stock) {
                        throw new \RuntimeException(
                            "موجودی محصول «{$product->name}» کافی نیست."
                        );
                    }

                    $currentUnitPrice = max(
                        0,
                        (float) $product->price
                        + (float) $variants->sum(
                            fn (ProductVariant $variant) => (float) $variant->price_adjustment
                        )
                    );

                    // Never silently charge a stale cart price. The customer must
                    // revisit/update the item to acknowledge its new price first.
                    if (round($currentUnitPrice, 2) !== round((float) $item->unit_price, 2)) {
                        throw new \RuntimeException(
                            "قیمت محصول «{$product->name}» تغییر کرده است؛ به سبد خرید برگردید و تعداد آن را دوباره تأیید کنید."
                        );
                    }

                    $subtotal += $currentUnitPrice * (int) $item->quantity;
                }

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

                foreach ($cart->items as $item) {

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

                return $order;
            });

            /*
            |--------------------------------------------------------------------------
            | Empty active cart only after successful transaction
            |--------------------------------------------------------------------------
            */

            $cart->items()->delete();

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
        Order $order
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
        ]);
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
                return redirect()
                    ->route('home')
                    ->with(
                        'error',
                        $result['message']
                        ?? 'پرداخت انجام نشد.'
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
        return $product->variants()
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
