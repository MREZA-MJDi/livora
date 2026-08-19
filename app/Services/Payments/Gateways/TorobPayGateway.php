<?php

namespace App\Services\Payments\Gateways;

use App\Models\Order;
use App\Models\Payment;

class TorobPayGateway extends AbstractGateway
{
    protected string $gateway = 'torobpay';

    protected function config(): array
    {
        return config(
            'payment.torobpay',
            []
        );
    }

    /**
     * Build TorobPay create-payment payload.
     *
     * IMPORTANT:
     * Field names must match the real TorobPay Merchant API.
     */
    protected function buildCreatePayload(
        Order $order,
        Payment $payment
    ): array {
        return [
            'merchant_id' =>
                config(
                    'payment.torobpay.merchant_id'
                ),

            'order_id' =>
                $order->order_number,

            /*
             * Use the amount stored in our local Payment
             * as the financial snapshot.
             */
            'amount' =>
                (int) round(
                    (float) $payment->amount
                ),

            'callback_url' =>
                config(
                    'payment.torobpay.callback_url'
                ),

            'customer' => [
                'first_name' =>
                    $order->first_name,

                'last_name' =>
                    $order->last_name,

                'phone' =>
                    $order->phone,

                'email' =>
                    $order->email,
            ],

            'metadata' => [
                'livora_order_id' =>
                    $order->id,

                'livora_payment_id' =>
                    $payment->id,

                'order_number' =>
                    $order->order_number,
            ],
        ];
    }

    protected function extractPaymentUrl(
        array $response
    ): ?string {
        return
            data_get($response, 'payment_url')
            ?? data_get($response, 'paymentUrl')
            ?? data_get($response, 'redirect_url')
            ?? data_get($response, 'redirectUrl')
            ?? data_get($response, 'url');
    }

    protected function extractAuthority(
        array $response
    ): ?string {
        $value =
            data_get($response, 'authority')
            ?? data_get($response, 'token')
            ?? data_get($response, 'payment_id');

        return $value !== null
            ? (string) $value
            : null;
    }

    protected function extractTransactionId(
        array $response
    ): ?string {
        $value =
            data_get(
                $response,
                'transaction_id'
            )
            ?? data_get(
                $response,
                'transactionId'
            )
            ?? data_get(
                $response,
                'reference_id'
            )
            ?? data_get(
                $response,
                'referenceId'
            );

        return $value !== null
            ? (string) $value
            : null;
    }

    /**
     * Extract verified amount from TorobPay response.
     */
    protected function extractAmount(
        array $response
    ): int|float|string|null {
        return
            data_get($response, 'amount')
            ?? data_get($response, 'data.amount')
            ?? data_get($response, 'result.amount');
    }

    protected function buildVerifyPayload(
        Payment $payment
    ): array {
        return [
            'merchant_id' =>
                config(
                    'payment.torobpay.merchant_id'
                ),

            'authority' =>
                $payment->authority,

            'transaction_id' =>
                $payment->transaction_id,

            'order_id' =>
                $payment->order->order_number,

            'amount' =>
                (int) round(
                    (float) $payment->amount
                ),
        ];
    }

    protected function buildStatusPayload(
        Payment $payment
    ): array {
        return [
            'merchant_id' =>
                config(
                    'payment.torobpay.merchant_id'
                ),

            'authority' =>
                $payment->authority,

            'transaction_id' =>
                $payment->transaction_id,

            'order_id' =>
                $payment->order->order_number,
        ];
    }

    protected function buildCancelPayload(
        Payment $payment
    ): array {
        return [
            'merchant_id' =>
                config(
                    'payment.torobpay.merchant_id'
                ),

            'authority' =>
                $payment->authority,

            'transaction_id' =>
                $payment->transaction_id,
        ];
    }

    protected function buildRefundPayload(
        Payment $payment
    ): array {
        return [
            'merchant_id' =>
                config(
                    'payment.torobpay.merchant_id'
                ),

            'authority' =>
                $payment->authority,

            'transaction_id' =>
                $payment->transaction_id,

            'amount' =>
                (int) round(
                    (float) $payment->amount
                ),
        ];
    }
}
