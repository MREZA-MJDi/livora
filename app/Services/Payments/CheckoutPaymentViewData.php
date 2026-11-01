<?php

namespace App\Services\Payments;

use App\Models\Order;

class CheckoutPaymentViewData
{
    /**
     * Build every payment-page display value in the controller layer so
     * Blade sections never depend on local variables from a nested @php block.
     *
     * @return array<string, mixed>
     */
    public function forOrder(Order $order): array
    {
        $gatewayPaymentsAllowed = ! app()->isProduction()
            || (bool) config('payment.production_ready', false);

        $gateways = [
            [
                'key' => 'digipay',
                'name' => 'DigiPay',
                'fa_name' => 'دیجی‌پی',
                'description' => 'پرداخت از طریق درگاه دیجی‌پی',
                'enabled' => $gatewayPaymentsAllowed && (bool) config('payment.digipay.enabled', false),
                'badge' => 'DIGIPAY',
            ],
            [
                'key' => 'snappay',
                'name' => 'SnappPay',
                'fa_name' => 'اسنپ‌پی',
                'description' => 'پرداخت از طریق درگاه اسنپ‌پی',
                'enabled' => $gatewayPaymentsAllowed && (bool) config('payment.snappay.enabled', false),
                'badge' => 'SNAPPAY',
            ],
            [
                'key' => 'torobpay',
                'name' => 'TorobPay',
                'fa_name' => 'ترب‌پی',
                'description' => 'پرداخت از طریق درگاه ترب‌پی',
                'enabled' => $gatewayPaymentsAllowed && (bool) config('payment.torobpay.enabled', false),
                'badge' => 'TOROBPAY',
            ],
        ];

        $installments = $order->installments;
        $cashInstallment = $installments->firstWhere('type', 'cash');
        $chequeInstallments = $installments->where('type', 'cheque')->values();

        return [
            'gatewayPaymentsAllowed' => $gatewayPaymentsAllowed,
            'gateways' => $gateways,
            'enabledGateways' => collect($gateways)->where('enabled', true)->values(),
            'orderTotal' => (float) $order->total,
            'hasInstallmentPlan' => (bool) $order->installment_enabled && $installments->isNotEmpty(),
            'cashInstallment' => $cashInstallment,
            'chequeInstallments' => $chequeInstallments,
            'cashPercent' => (int) ($order->installment_cash_percent ?? 0),
            'cashAmount' => $cashInstallment ? (float) $cashInstallment->amount : null,
            'deferredAmount' => (float) ($order->installment_deferred_amount ?? 0),
            'chequeCount' => $chequeInstallments->count(),
            'intervalMonths' => (int) ($order->installment_interval_months ?? 0),
            'isPaid' => $order->payment_status === 'paid',
            'latestPayment' => $order->latestPayment,
        ];
    }
}
