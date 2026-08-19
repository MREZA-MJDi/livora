<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\InstallmentPaymentGatewayInterface;
use App\Services\Payments\Gateways\DigiPayGateway;
use App\Services\Payments\Gateways\SnapPayGateway;
use App\Services\Payments\Gateways\TorobPayGateway;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * Resolve an installment payment gateway.
     */
    public function driver(
        string $gateway
    ): InstallmentPaymentGatewayInterface {
        return match (strtolower($gateway)) {
            'digipay' =>
            app(DigiPayGateway::class),

            'snappay' =>
            app(SnapPayGateway::class),

            'torobpay' =>
            app(TorobPayGateway::class),

            default =>
            throw new InvalidArgumentException(
                "Unsupported payment gateway [{$gateway}]."
            ),
        };
    }
}
