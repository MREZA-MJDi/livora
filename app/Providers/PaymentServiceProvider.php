<?php

namespace App\Providers;

use App\Services\Payments\Contracts\InstallmentPaymentGatewayInterface;
use App\Services\Payments\Gateways\DigiPayGateway;
use App\Services\Payments\Gateways\SnapPayGateway;
use App\Services\Payments\Gateways\TorobPayGateway;
use App\Services\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class, function ($app) {
            return new PaymentManager([
                'digipay' => $app->make(DigiPayGateway::class),
                'snappay' => $app->make(SnapPayGateway::class),
                'torobpay' => $app->make(TorobPayGateway::class),
            ]);
        });

        // The interface represents one provider contract; PaymentManager
        // selects the concrete driver at runtime by gateway key.
        $this->app->bind(
            InstallmentPaymentGatewayInterface::class,
            fn ($app) => $app->make(PaymentManager::class)->driver(
                config('payment.default', 'digipay')
            )
        );
    }
}
