<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\InstallmentPaymentGatewayInterface;
use InvalidArgumentException;

class PaymentManager
{
    /**
     * @param array<string, InstallmentPaymentGatewayInterface> $drivers
     */
    public function __construct(
        protected array $drivers
    ) {
        foreach ($this->drivers as $key => $driver) {
            if (! $driver instanceof InstallmentPaymentGatewayInterface) {
                throw new InvalidArgumentException(
                    "Payment driver [{$key}] must implement InstallmentPaymentGatewayInterface."
                );
            }
        }
    }

    public function driver(string $gateway): InstallmentPaymentGatewayInterface
    {
        $gateway = $this->normalize($gateway);

        if (! isset($this->drivers[$gateway])) {
            throw new InvalidArgumentException(
                "Unsupported payment gateway [{$gateway}]."
            );
        }

        return $this->drivers[$gateway];
    }

    public function supports(string $gateway): bool
    {
        return isset($this->drivers[$this->normalize($gateway)]);
    }

    /**
     * Return configured online payment methods for the checkout UI.
     *
     * @return array<int, array{
     *     key:string,
     *     name:string,
     *     fa_name:string,
     *     description:string,
     *     enabled:bool,
     *     badge:string
     * }>
     */
    public function onlineMethods(): array
    {
        $definitions = [
            'digipay' => [
                'name' => 'DigiPay',
                'fa_name' => 'دیجی‌پی',
                'description' => 'پرداخت آنلاین از طریق دیجی‌پی',
                'badge' => 'DIGIPAY',
            ],
            'snappay' => [
                'name' => 'SnappPay',
                'fa_name' => 'اسنپ‌پی',
                'description' => 'پرداخت آنلاین از طریق اسنپ‌پی',
                'badge' => 'SNAPPAY',
            ],
            'torobpay' => [
                'name' => 'TorobPay',
                'fa_name' => 'ترب‌پی',
                'description' => 'پرداخت آنلاین از طریق ترب‌پی',
                'badge' => 'TOROBPAY',
            ],
        ];

        return collect($definitions)
            ->map(function (array $definition, string $key): array {
                return [
                    'key' => $key,
                    ...$definition,
                    'enabled' => (bool) config("payment.{$key}.enabled", false),
                ];
            })
            ->values()
            ->all();
    }

    public function normalize(string $gateway): string
    {
        return strtolower(trim($gateway));
    }
}
