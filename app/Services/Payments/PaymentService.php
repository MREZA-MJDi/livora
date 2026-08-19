<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PaymentService
{
    public function __construct(
        protected PaymentManager $paymentManager
    ) {
    }

    /**
     * Start an external installment payment.
     *
     * This method is only responsible for:
     *
     * Order
     *   ↓
     * Local Payment
     *   ↓
     * External Gateway
     *
     * Internal Livora installment plans are handled by
     * InstallmentPlanService.
     */
    public function startInstallmentPayment(
        Order $order,
        string $gateway
    ): array {
        $this->validateOrderForPayment($order);

        $gateway = $this->normalizeGateway($gateway);

        /*
         * Resolve gateway first.
         *
         * This also guarantees that the requested gateway
         * is actually supported by our PaymentManager.
         */
        $gatewayService = $this->paymentManager->driver($gateway);

        /*
         * Always use our own order total.
         *
         * Never trust an amount from the frontend.
         */
        $amount = $this->normalizeAmount(
            $order->total
        );

        /*
         * Create local payment record before
         * communicating with the external provider.
         */
        $payment = DB::transaction(
            function () use (
                $order,
                $gateway,
                $amount
            ) {
                $payment = Payment::create([
                    'order_id' =>
                        $order->id,

                    'user_id' =>
                        $order->user_id,

                    'gateway' =>
                        $gateway,

                    'amount' =>
                        $amount,

                    'status' =>
                        'pending',

                    'metadata' => [
                        'payment_method' =>
                            'installment',

                        'order_number' =>
                            $order->order_number,
                    ],
                ]);

                $order->update([
                    'payment_method' =>
                        'installment',

                    'payment_provider' =>
                        $gateway,

                    'payment_status' =>
                        'pending',
                ]);

                return $payment;
            }
        );

        try {
            $result = $gatewayService->createPayment(
                $order,
                $payment
            );

            if (! ($result['success'] ?? false)) {
                $this->markPaymentAsFailed(
                    $payment,
                    $result['message']
                    ?? 'Payment initialization failed.',
                    [
                        'gateway' =>
                            $gateway,

                        'response' =>
                            $result,
                    ]
                );

                return [
                    'success' =>
                        false,

                    'payment' =>
                        $payment->fresh(),

                    'gateway' =>
                        $gateway,

                    'redirect_url' =>
                        null,

                    'message' =>
                        $result['message']
                        ?? 'Payment initialization failed.',

                    'data' =>
                        $result['data'] ?? [],
                ];
            }

            $this->markPaymentAsInitiated(
                $payment,
                $result
            );

            return [
                'success' =>
                    true,

                'payment' =>
                    $payment->fresh(),

                'gateway' =>
                    $gateway,

                'redirect_url' =>
                    $result['payment_url']
                    ?? null,

                'message' =>
                    $result['message']
                    ?? null,

                'data' =>
                    $result['data'] ?? [],
            ];
        } catch (Throwable $e) {
            report($e);

            $this->markPaymentAsFailed(
                $payment,
                $e->getMessage(),
                [
                    'exception' =>
                        $e::class,
                ]
            );

            return [
                'success' =>
                    false,

                'payment' =>
                    $payment->fresh(),

                'gateway' =>
                    $gateway,

                'redirect_url' =>
                    null,

                'message' =>
                    'خطا در اتصال به درگاه پرداخت.',

                'data' =>
                    [],
            ];
        }
    }

    /**
     * Handle a normalized gateway callback.
     */
    public function handleCallback(
        string $gateway,
        array $callbackData
    ): array {
        $gateway = $this->normalizeGateway($gateway);

        $gatewayService = $this->paymentManager->driver(
            $gateway
        );

        $payment = $this->findPaymentFromCallback(
            $gateway,
            $callbackData
        );

        if (! $payment) {
            return [
                'success' =>
                    false,

                'order_id' =>
                    null,

                'payment_id' =>
                    null,

                'message' =>
                    'Payment record could not be identified.',

                'data' =>
                    $callbackData,
            ];
        }

        /*
         * Idempotency:
         * Don't verify an already-paid payment again.
         */
        if ($payment->isPaid()) {
            return [
                'success' =>
                    true,

                'order_id' =>
                    $payment->order_id,

                'payment_id' =>
                    $payment->id,

                'transaction_id' =>
                    $payment->transaction_id,

                'message' =>
                    'Payment was already completed.',

                'data' => [
                    'already_paid' =>
                        true,
                ],
            ];
        }

        try {
            /*
             * Gateway is responsible for translating its own
             * callback format into our normalized result.
             */
            $result = $gatewayService->verifyPayment(
                $payment
            );

            if (! ($result['success'] ?? false)) {
                $this->markPaymentAsFailed(
                    $payment,
                    $result['message']
                    ?? 'Payment verification failed.',
                    [
                        'callback' =>
                            $callbackData,

                        'verify_response' =>
                            $result,
                    ]
                );

                return [
                    'success' =>
                        false,

                    'order_id' =>
                        $payment->order_id,

                    'payment_id' =>
                        $payment->id,

                    'message' =>
                        $result['message']
                        ?? 'Payment verification failed.',

                    'data' =>
                        $result['data'] ?? [],
                ];
            }

            /*
             * Never trust a successful gateway response blindly.
             */
            $this->validateVerifiedAmount(
                $payment,
                $result
            );

            $this->markPaymentAsPaid(
                $payment,
                $result['transaction_id']
                ?? null,
                [
                    'callback' =>
                        $callbackData,

                    'verify_response' =>
                        $result,
                ]
            );

            $freshPayment =
                $payment->fresh();

            return [
                'success' =>
                    true,

                'order_id' =>
                    $freshPayment->order_id,

                'payment_id' =>
                    $freshPayment->id,

                'transaction_id' =>
                    $freshPayment->transaction_id,

                'message' =>
                    $result['message']
                    ?? 'Payment verified successfully.',

                'data' =>
                    $result['data'] ?? [],
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' =>
                    false,

                'order_id' =>
                    $payment->order_id,

                'payment_id' =>
                    $payment->id,

                'message' =>
                    'خطا در تأیید پرداخت.',

                'data' =>
                    [],
            ];
        }
    }

    /**
     * Verify an existing payment.
     */
    public function verifyPayment(
        Payment $payment
    ): array {
        if ($payment->isPaid()) {
            return [
                'success' =>
                    true,

                'payment' =>
                    $payment,

                'order' =>
                    $payment->order,

                'message' =>
                    'Payment is already paid.',
            ];
        }

        $gateway = $this->normalizeGateway(
            $payment->gateway
        );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            $result =
                $gatewayService->verifyPayment(
                    $payment
                );

            if (! ($result['success'] ?? false)) {
                $this->markPaymentAsFailed(
                    $payment,
                    $result['message']
                    ?? 'Payment verification failed.',
                    [
                        'verify_response' =>
                            $result,
                    ]
                );

                return [
                    'success' =>
                        false,

                    'payment' =>
                        $payment->fresh(),

                    'order' =>
                        $payment->order?->fresh(),

                    'message' =>
                        $result['message']
                        ?? 'Payment verification failed.',
                ];
            }

            $this->validateVerifiedAmount(
                $payment,
                $result
            );

            $this->markPaymentAsPaid(
                $payment,
                $result['transaction_id']
                ?? null,
                [
                    'verify_response' =>
                        $result,
                ]
            );

            return [
                'success' =>
                    true,

                'payment' =>
                    $payment->fresh(),

                'order' =>
                    $payment->order?->fresh(),

                'message' =>
                    $result['message']
                    ?? 'Payment verified successfully.',
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' =>
                    false,

                'payment' =>
                    $payment->fresh(),

                'order' =>
                    $payment->order?->fresh(),

                'message' =>
                    'خطا در تأیید پرداخت.',
            ];
        }
    }

    /**
     * Get payment status from the gateway.
     */
    public function getStatus(
        Payment $payment
    ): array {
        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            return $gatewayService->getStatus(
                $payment
            );
        } catch (Throwable $e) {
            report($e);

            return [
                'success' =>
                    false,

                'status' =>
                    'unknown',

                'message' =>
                    'Unable to fetch payment status.',

                'data' =>
                    [],
            ];
        }
    }

    /**
     * Cancel unpaid payment.
     */
    public function cancelPayment(
        Payment $payment
    ): array {
        if ($payment->isPaid()) {
            return [
                'success' =>
                    false,

                'message' =>
                    'Paid payments cannot be cancelled.',
            ];
        }

        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            $result =
                $gatewayService->cancelPayment(
                    $payment
                );

            if ($result['success'] ?? false) {
                $payment->update([
                    'status' =>
                        'cancelled',

                    'metadata' =>
                        array_merge(
                            $payment->metadata ?? [],
                            [
                                'cancel_response' =>
                                    $result,
                            ]
                        ),
                ]);
            }

            return $result;
        } catch (Throwable $e) {
            report($e);

            return [
                'success' =>
                    false,

                'message' =>
                    'Payment cancellation failed.',

                'data' =>
                    [],
            ];
        }
    }

    /**
     * Refund a paid payment.
     */
    public function refundPayment(
        Payment $payment
    ): array {
        if (! $payment->isPaid()) {
            return [
                'success' =>
                    false,

                'message' =>
                    'Only paid payments can be refunded.',
            ];
        }

        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            $result =
                $gatewayService->refundPayment(
                    $payment
                );

            if ($result['success'] ?? false) {
                DB::transaction(
                    function () use (
                        $payment,
                        $result
                    ) {
                        $payment->update([
                            'status' =>
                                'refunded',

                            'metadata' =>
                                array_merge(
                                    $payment->metadata ?? [],
                                    [
                                        'refund_response' =>
                                            $result,
                                    ]
                                ),
                        ]);

                        $order =
                            $payment->order;

                        if ($order) {
                            $order->update([
                                'payment_status' =>
                                    'refunded',
                            ]);
                        }
                    }
                );
            }

            return $result;
        } catch (Throwable $e) {
            report($e);

            return [
                'success' =>
                    false,

                'message' =>
                    'Payment refund failed.',

                'data' =>
                    [],
            ];
        }
    }

    /**
     * Mark payment as initiated.
     */
    protected function markPaymentAsInitiated(
        Payment $payment,
        array $result
    ): void {
        $payment->update([
            'status' =>
                'initiated',

            'authority' =>
                $result['authority']
                ?? null,

            'transaction_id' =>
                $result['transaction_id']
                ?? null,

            'metadata' =>
                array_merge(
                    $payment->metadata ?? [],
                    [
                        'create_response' =>
                            $result,
                    ]
                ),
        ]);
    }

    /**
     * Mark payment as paid.
     */
    protected function markPaymentAsPaid(
        Payment $payment,
        ?string $transactionId = null,
        array $extraMetadata = []
    ): void {
        DB::transaction(
            function () use (
                $payment,
                $transactionId,
                $extraMetadata
            ) {
                $lockedPayment =
                    Payment::query()
                        ->whereKey($payment->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                 * Idempotency.
                 */
                if ($lockedPayment->status === 'paid') {
                    return;
                }

                $lockedPayment->update([
                    'status' =>
                        'paid',

                    'transaction_id' =>
                        $transactionId
                        ?? $lockedPayment->transaction_id,

                    'paid_at' =>
                        now(),

                    'metadata' =>
                        array_merge(
                            $lockedPayment->metadata ?? [],
                            $extraMetadata
                        ),
                ]);

                $order =
                    $lockedPayment->order;

                if (! $order) {
                    throw new RuntimeException(
                        'Payment order was not found.'
                    );
                }

                $order->update([
                    'payment_status' =>
                        'paid',
                ]);
            }
        );
    }

    /**
     * Mark payment as failed.
     */
    protected function markPaymentAsFailed(
        Payment $payment,
        ?string $message = null,
        array $data = []
    ): void {
        $payment->update([
            'status' =>
                'failed',

            'metadata' =>
                array_merge(
                    $payment->metadata ?? [],
                    [
                        'failure_message' =>
                            $message,

                        'failure_response' =>
                            $data,
                    ]
                ),
        ]);

        $order =
            $payment->order;

        if (! $order) {
            return;
        }

        /*
         * Do not overwrite a successful payment state
         * caused by another payment attempt.
         */
        $hasSuccessfulPayment =
            $order->payments()
                ->where(
                    'id',
                    '!=',
                    $payment->id
                )
                ->where(
                    'status',
                    'paid'
                )
                ->exists();

        if (! $hasSuccessfulPayment) {
            $order->update([
                'payment_status' =>
                    'failed',
            ]);
        }
    }

    /**
     * Find the local payment from callback data.
     *
     * Gateway implementations should normalize provider-specific
     * callback values as much as possible.
     */
    protected function findPaymentFromCallback(
        string $gateway,
        array $callbackData
    ): ?Payment {
        /*
         * 1. Our local payment ID.
         */
        foreach ([
                     'payment_id',
                     'paymentId',
                 ] as $key) {
            if (
                isset($callbackData[$key])
                && is_numeric($callbackData[$key])
            ) {
                $payment =
                    Payment::query()
                        ->whereKey(
                            (int) $callbackData[$key]
                        )
                        ->where(
                            'gateway',
                            $gateway
                        )
                        ->first();

                if ($payment) {
                    return $payment;
                }
            }
        }

        /*
         * 2. Gateway authority / transaction / token.
         */
        foreach ([
                     'authority',
                     'Authority',
                     'transaction_id',
                     'transactionId',
                     'tracking_code',
                     'trackingCode',
                     'token',
                     'Token',
                     'reference_id',
                     'referenceId',
                     'invoice_id',
                     'invoiceId',
                 ] as $key) {
            if (
                ! array_key_exists(
                    $key,
                    $callbackData
                )
                || blank($callbackData[$key])
            ) {
                continue;
            }

            $value =
                (string) $callbackData[$key];

            $payment =
                Payment::query()
                    ->where(
                        'gateway',
                        $gateway
                    )
                    ->where(
                        function ($query) use ($value) {
                            $query
                                ->where(
                                    'authority',
                                    $value
                                )
                                ->orWhere(
                                    'transaction_id',
                                    $value
                                );
                        }
                    )
                    ->latest('id')
                    ->first();

            if ($payment) {
                return $payment;
            }
        }

        /*
         * 3. Our own order number.
         */
        foreach ([
                     'order_number',
                     'orderNumber',
                     'merchant_order_id',
                     'merchantOrderId',
                 ] as $key) {
            if (
                ! array_key_exists(
                    $key,
                    $callbackData
                )
                || blank($callbackData[$key])
            ) {
                continue;
            }

            $order =
                Order::query()
                    ->where(
                        'order_number',
                        $callbackData[$key]
                    )
                    ->first();

            if (! $order) {
                continue;
            }

            return $order->payments()
                ->where(
                    'gateway',
                    $gateway
                )
                ->latest('id')
                ->first();
        }

        return null;
    }

    /**
     * Verify returned amount against our own records.
     */
    protected function validateVerifiedAmount(
        Payment $payment,
        array $result
    ): void {
        /*
         * If gateway returned an amount,
         * compare it with our Payment amount.
         */
        if (
            array_key_exists(
                'amount',
                $result
            )
            && $result['amount'] !== null
        ) {
            $expected =
                $this->normalizeAmount(
                    $payment->amount
                );

            $received =
                $this->normalizeAmount(
                    $result['amount']
                );

            if ($expected !== $received) {
                throw new RuntimeException(
                    'Payment amount mismatch.'
                );
            }
        }

        $order =
            $payment->order;

        if (! $order) {
            throw new RuntimeException(
                'Payment order was not found.'
            );
        }

        $paymentAmount =
            $this->normalizeAmount(
                $payment->amount
            );

        $orderAmount =
            $this->normalizeAmount(
                $order->total
            );

        if (
            $paymentAmount !== $orderAmount
        ) {
            throw new RuntimeException(
                'Payment amount does not match order total.'
            );
        }
    }

    /**
     * Validate order state.
     */
    protected function validateOrderForPayment(
        Order $order
    ): void {
        if (
            $order->payment_status === 'paid'
        ) {
            throw new RuntimeException(
                'این سفارش قبلاً پرداخت شده است.'
            );
        }

        if (
            $order->status === 'cancelled'
        ) {
            throw new RuntimeException(
                'سفارش لغو شده قابل پرداخت نیست.'
            );
        }

        if (
            (float) $order->total <= 0
        ) {
            throw new RuntimeException(
                'مبلغ سفارش معتبر نیست.'
            );
        }
    }

    /**
     * Normalize gateway name.
     */
    protected function normalizeGateway(
        string $gateway
    ): string {
        return strtolower(
            trim($gateway)
        );
    }

    /**
     * Normalize monetary values.
     */
    protected function normalizeAmount(
        mixed $amount
    ): int {
        return (int) round(
            (float) $amount
        );
    }
}
