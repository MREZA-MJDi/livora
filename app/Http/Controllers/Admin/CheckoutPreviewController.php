<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payments\CheckoutPaymentViewData;
use Illuminate\View\View;

class CheckoutPreviewController extends Controller
{
    /**
     * Render a safe, non-persistent payment-page preview for administrators.
     *
     * The preview never creates an order, payment attempt, installment plan,
     * inventory reservation, or customer-facing transaction.
     */
    public function show(CheckoutPaymentViewData $viewData): View
    {
        $order = new Order([
            'order_number' => 'PREVIEW-SG-0001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => 48500000,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 48500000,
            'first_name' => 'مشتری نمونه',
            'last_name' => 'سیلاگالری',
            'phone' => '09120000000',
            'email' => 'preview@example.invalid',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'این آدرس صرفاً نمونه است و سفارش واقعی نیست.',
            'postal_code' => '0000000000',
            'unit' => '۱',
            'installment_enabled' => false,
        ]);

        $item = new OrderItem([
            'product_name' => 'مبل راحتی مدرن — نمونه نمایشی',
            'sku' => 'PREVIEW-SKU',
            'quantity' => 1,
            'unit_price' => 48500000,
            'total' => 48500000,
            'variant_options' => [
                ['name' => 'رنگ', 'value' => 'طوسی'],
                ['name' => 'جنس', 'value' => 'پارچه'],
            ],
        ]);

        $item->setRelation('product', null);

        $order->setRelation('items', collect([$item]));
        $order->setRelation('latestPayment', null);
        $order->setRelation('installments', collect());

        return view('checkout.payment', [
            'order' => $order,
            'preview' => true,
        ] + $viewData->forOrder($order));
    }
}
