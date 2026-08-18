<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
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
            $total = $subtotal + $shippingCost - $discount;

            $order = Order::create([
                'user_id' => Auth::id(),

                'order_number' => $this->generateOrderNumber(),

                'status' => 'pending',
                'payment_status' => 'pending',

                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => $total,

                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],

                'province' => $validated['province'],
                'city' => $validated['city'],
                'address' => $validated['address'],
                'postal_code' => $validated['postal_code'],
                'unit' => $validated['unit'] ?? null,

                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->variant?->sku
                        ?? $item->product->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' =>
                        (float) $item->unit_price
                        * $item->quantity,
                ]);
            }

            Payment::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'gateway' => 'pending',
                'amount' => $total,
                'status' => 'pending',
            ]);

            return $order;
        });

        $cart->items()->delete();

        return redirect()
            ->route('checkout.payment', $order)
            ->with(
                'success',
                'سفارش با موفقیت ایجاد شد.'
            );
    }

    public function payment(Order $order): View
    {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'latestPayment',
        ]);

        return view('checkout.payment', [
            'order' => $order,
        ]);
    }

    protected function getActiveCart(): Cart
    {
        return Cart::query()
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'active',
                ],
                [
                    'session_id' => null,
                ]
            );
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number =
                'LV-' .
                now()->format('ymd') .
                '-' .
                strtoupper(Str::random(6));
        } while (
            Order::query()
                ->where('order_number', $number)
                ->exists()
        );

        return $number;
    }
}
