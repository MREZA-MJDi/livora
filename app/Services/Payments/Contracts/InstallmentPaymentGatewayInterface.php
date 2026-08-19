<?php

namespace App\Services\Payments\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface InstallmentPaymentGatewayInterface
{
    /**
     * Create an installment payment request.
     *
     * @return array{
     *     success: bool,
     *     payment_url: ?string,
     *     authority: ?string,
     *     transaction_id: ?string,
     *     amount: ?int|float,
     *     message: ?string,
     *     data: array
     * }
     */
    public function createPayment(
        Order $order,
        Payment $payment
    ): array;

    /**
     * Verify an installment payment.
     *
     * The Gateway is responsible for communicating
     * with its provider and returning a normalized result.
     *
     * @return array{
     *     success: bool,
     *     transaction_id: ?string,
     *     amount: ?int|float,
     *     message: ?string,
     *     data: array
     * }
     */
    public function verifyPayment(
        Payment $payment
    ): array;

    /**
     * Get current payment status.
     *
     * @return array{
     *     success: bool,
     *     status: string,
     *     message: ?string,
     *     data: array
     * }
     */
    public function getStatus(
        Payment $payment
    ): array;

    /**
     * Cancel an unpaid payment.
     *
     * @return array{
     *     success: bool,
     *     message: ?string,
     *     data: array
     * }
     */
    public function cancelPayment(
        Payment $payment
    ): array;

    /**
     * Refund a paid payment.
     *
     * @return array{
     *     success: bool,
     *     message: ?string,
     *     data: array
     * }
     */
    public function refundPayment(
        Payment $payment
    ): array;
}
