<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
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
     * Create order from the active cart.
     */
    public function placeOrder(
        PlaceOrderRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $cart = $this->getActiveCart();

        $cart->load([
            'items.product',
            'items.variant',
        ]);

        abort_if(
            $cart->items->isEmpty(),
            422,
            'سبد خرید خالی است.'
        );

        $order = DB::transaction(function () use (
            $cart,
            $validated
        ) {
            $subtotal = 0;

            foreach ($cart->items as $item) {
                $stock = $item->variant?->stock
                    ?? $item->product->stock;

                if ($item->quantity > $stock) {
                    abort(
                        422,
                        "موجودی محصول {$item->product->name} کافی نیست."
                    );
                }

                $subtotal +=
                    (float) $item->unit_price
                    * $item->quantity;
            }

            $shippingCost = 0;
            $discount = 0;

            $total = $subtotal
                + $shippingCost
                - $discount;

            $order = Order::create([
                'user_id' => Auth::id(),

                'order_number' =>
                    $this->generateOrderNumber(),

                'status' => 'pending',
                'payment_status' => 'pending',

                /*
                 * Payment is selected after
                 * order creation.
                 */
                'payment_method' => 'online',
                'payment_provider' => null,

                /*
                 * Internal installment snapshot.
                 * These remain empty until the
                 * customer chooses Livora installment.
                 */
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

            /*
             * Create order items.
             */
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' =>
                        $item->product_id,

                    'product_variant_id' =>
                        $item->product_variant_id,

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
                        * $item->quantity,
                ]);
            }

            /*
             * Create the first local payment attempt.
             *
             * The actual gateway will be selected
             * later on the payment page.
             */
            Payment::create([
                'order_id' =>
                    $order->id,

                'user_id' =>
                    Auth::id(),

                'gateway' =>
                    'pending',

                'amount' =>
                    $total,

                'status' =>
                    'pending',

                'metadata' => [
                    'payment_method' =>
                        'pending',

                    'order_number' =>
                        $order->order_number,
                ],
            ]);

            return $order;
        });

        /*
         * Clear active cart after order creation.
         */
        $cart->items()->delete();

        return redirect()
            ->route(
                'checkout.payment',
                $order
            )
            ->with(
                'success',
                'سفارش با موفقیت ایجاد شد.'
            );
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
            $installmentPlanService->create(
                $order
            );

            return redirect()
                ->route(
                    'checkout.payment',
                    $order
                )
                ->with(
                    'success',
                    'طرح خرید اقساطی برای سفارش ایجاد شد.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
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
