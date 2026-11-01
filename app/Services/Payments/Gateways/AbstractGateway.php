<?php

namespace App\Services\Payments\Gateways;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\Contracts\InstallmentPaymentGatewayInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

abstract class AbstractGateway implements InstallmentPaymentGatewayInterface
{
    protected string $gateway;

    /**
     * Return provider configuration from config/payment.php.
     */
    abstract protected function config(): array;

    /**
     * Build provider-specific create-payment payload.
     */
    abstract protected function buildCreatePayload(
        Order $order,
        Payment $payment
    ): array;

    /**
     * Extract redirect URL from provider response.
     */
    abstract protected function extractPaymentUrl(
        array $response
    ): ?string;

    /**
     * Extract provider authority/reference.
     */
    abstract protected function extractAuthority(
        array $response
    ): ?string;

    /**
     * Extract transaction ID from provider response.
     */
    abstract protected function extractTransactionId(
        array $response
    ): ?string;

    /**
     * Build verify payload.
     */
    abstract protected function buildVerifyPayload(
        Payment $payment
    ): array;

    /**
     * Build status payload.
     */
    abstract protected function buildStatusPayload(
        Payment $payment
    ): array;

    /**
     * Build cancel payload.
     */
    abstract protected function buildCancelPayload(
        Payment $payment
    ): array;

    /**
     * Build refund payload.
     */
    abstract protected function buildRefundPayload(
        Payment $payment
    ): array;

    /**
     * Extract verified amount from provider response.
     */
    protected function extractAmount(
        array $response
    ): int|float|string|null {
        return data_get($response, 'amount');
    }

    /**
     * Create configured HTTP client.
     */
    protected function client(): PendingRequest
    {
        $config = $this->config();

        $baseUrl = rtrim(
            (string) ($config['base_url'] ?? ''),
            '/'
        );

        if ($baseUrl === '') {
            throw new RuntimeException(
                "{$this->gateway} base URL is not configured."
            );
        }

        if (
            app()->isProduction()
            && strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) !== 'https'
        ) {
            throw new RuntimeException(
                "{$this->gateway} must use HTTPS in production."
            );
        }

        $request = Http::acceptJson()
            ->asJson()
            ->timeout(
                (int) ($config['timeout'] ?? 30)
            );

        /*
         * Optional Bearer authentication.
         */
        if (! empty($config['access_token'])) {
            $request = $request->withToken(
                $config['access_token']
            );
        }

        /*
         * Generic headers.
         * Providers can add their own headers in child classes.
         */
        $headers = array_filter([
            'X-Merchant-Id' =>
                $config['merchant_id'] ?? null,

            'X-Api-Key' =>
                $config['api_key'] ?? null,
        ]);

        if ($headers !== []) {
            $request = $request->withHeaders(
                $headers
            );
        }

        return $request;
    }

    /**
     * Perform POST request against configured provider endpoint.
     */
    protected function post(
        string $endpoint,
        array $payload
    ): array {
        $config = $this->config();

        $baseUrl = rtrim(
            (string) ($config['base_url'] ?? ''),
            '/'
        );

        if ($baseUrl === '') {
            return [
                'success' => false,
                'message' =>
                    "{$this->gateway} base URL is not configured.",
                'status_code' => 0,
                'data' => [],
            ];
        }

        $endpoint = '/' . ltrim(
                $endpoint,
                '/'
            );

        try {
            $response = $this->client()->post(
                $baseUrl . $endpoint,
                $payload
            );

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' =>
                        $response->json('message')
                        ?? 'Gateway request failed.',
                    'status_code' =>
                        $response->status(),
                    'data' =>
                        $response->json() ?? [],
                ];
            }

            return [
                'success' => true,
                'message' =>
                    $response->json('message'),
                'status_code' =>
                    $response->status(),
                'data' =>
                    $response->json() ?? [],
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' =>
                    'Gateway connection failed.',
                'status_code' => 0,
                'data' => [],
            ];
        }
    }

    /**
     * Create installment payment.
     */
    public function createPayment(
        Order $order,
        Payment $payment
    ): array {
        $config = $this->config();

        if (app()->isProduction() && ! config('payment.production_ready', false)) {
            return [
                'success' => false,
                'payment_url' => null,
                'authority' => null,
                'transaction_id' => null,
                'message' => 'این اتصال درگاه تا زمان تأیید قرارداد واقعی API و تست محیط آزمایشی برای تولید فعال نشده است.',
                'data' => [],
            ];
        }

        if (($config['enabled'] ?? false) !== true) {
            return [
                'success' => false,
                'payment_url' => null,
                'authority' => null,
                'transaction_id' => null,
                'message' =>
                    "{$this->gateway} is disabled.",
                'data' => [],
            ];
        }

        $endpoint = (string) (
            $config['endpoints']['create'] ?? ''
        );

        if ($endpoint === '') {
            return [
                'success' => false,
                'payment_url' => null,
                'authority' => null,
                'transaction_id' => null,
                'message' =>
                    "{$this->gateway} create endpoint is not configured.",
                'data' => [],
            ];
        }

        try {
            $payload = $this->buildCreatePayload(
                $order,
                $payment
            );

            $result = $this->post(
                $endpoint,
                $payload
            );

            if (! $result['success']) {
                $statusCode = (int) ($result['status_code'] ?? 0);

                return [
                    'success' => false,
                    'payment_url' => null,
                    'authority' => null,
                    'transaction_id' => null,
                    'retryable' => $statusCode === 0 || $statusCode === 429 || $statusCode >= 500,
                    'status_code' => $statusCode,
                    'message' => $result['message'],
                    'data' => $result['data'],
                ];
            }

            $response =
                $result['data'];

            $paymentUrl = $this->extractPaymentUrl($response);

            if (app()->isProduction()) {
                $scheme = strtolower((string) parse_url((string) $paymentUrl, PHP_URL_SCHEME));
                $host = strtolower((string) parse_url((string) $paymentUrl, PHP_URL_HOST));
                $allowedHosts = array_values(array_filter(
                    array_map(
                        static fn ($allowedHost) => strtolower(trim((string) $allowedHost)),
                        $config['allowed_redirect_hosts'] ?? []
                    )
                ));

                if (
                    $scheme !== 'https'
                    || $host === ''
                    || $allowedHosts === []
                    || ! in_array($host, $allowedHosts, true)
                ) {
                    return [
                        'success' => false,
                        'payment_url' => null,
                        'authority' => $this->extractAuthority($response),
                        'transaction_id' => $this->extractTransactionId($response),
                        'message' => 'نشانی بازگشت درگاه با فهرست دامنه‌های HTTPS تأییدشده مطابقت ندارد.',
                        'data' => [
                            'redirect_host' => $host ?: null,
                            'allowlist_configured' => $allowedHosts !== [],
                        ],
                    ];
                }
            }

            return [
                'success' =>
                    $paymentUrl !== null,

                'payment_url' =>
                    $paymentUrl,

                'authority' =>
                    $this->extractAuthority(
                        $response
                    ),

                'transaction_id' =>
                    $this->extractTransactionId(
                        $response
                    ),

                'message' =>
                    $result['message']
                    ?? 'Payment initialized.',

                'data' =>
                    $response,
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'payment_url' => null,
                'authority' => null,
                'transaction_id' => null,
                'message' =>
                    'Gateway payment initialization failed.',
                'data' => [],
            ];
        }
    }

    /**
     * Verify payment.
     */
    public function verifyPayment(
        Payment $payment
    ): array {
        $endpoint = (string) (
            $this->config()['endpoints']['verify']
            ?? ''
        );

        if ($endpoint === '') {
            return [
                'success' => false,
                'amount' => null,
                'transaction_id' => null,
                'message' =>
                    "{$this->gateway} verify endpoint is not configured.",
                'data' => [],
            ];
        }

        try {
            $result = $this->post(
                $endpoint,
                $this->buildVerifyPayload(
                    $payment
                )
            );

            if (! $result['success']) {
                return [
                    'success' => false,
                    'amount' => null,
                    'transaction_id' => null,
                    'message' =>
                        $result['message'],
                    'data' =>
                        $result['data'],
                ];
            }

            $response =
                $result['data'];

            return [
                'success' =>
                    true,

                'amount' =>
                    $this->extractAmount(
                        $response
                    ),

                'transaction_id' =>
                    $this->extractTransactionId(
                        $response
                    ),

                'message' =>
                    $result['message']
                    ?? 'Payment verified.',

                'data' =>
                    $response,
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'amount' => null,
                'transaction_id' => null,
                'message' =>
                    'Gateway verification failed.',
                'data' => [],
            ];
        }
    }

    /**
     * Get payment status.
     */
    public function getStatus(
        Payment $payment
    ): array {
        $endpoint = (string) (
            $this->config()['endpoints']['status']
            ?? ''
        );

        if ($endpoint === '') {
            return [
                'success' => false,
                'status' => 'unknown',
                'message' =>
                    "{$this->gateway} status endpoint is not configured.",
                'data' => [],
            ];
        }

        try {
            $result = $this->post(
                $endpoint,
                $this->buildStatusPayload(
                    $payment
                )
            );

            return [
                'success' =>
                    $result['success'],

                'status' =>
                    data_get(
                        $result['data'],
                        'status',
                        'unknown'
                    ),

                'message' =>
                    $result['message'],

                'data' =>
                    $result['data'],
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' =>
                    'Unable to fetch payment status.',
                'data' => [],
            ];
        }
    }

    /**
     * Cancel payment.
     */
    public function cancelPayment(
        Payment $payment
    ): array {
        $endpoint = (string) (
            $this->config()['endpoints']['cancel']
            ?? ''
        );

        if ($endpoint === '') {
            return [
                'success' => false,
                'message' =>
                    "{$this->gateway} cancel endpoint is not configured.",
                'data' => [],
            ];
        }

        try {
            $result = $this->post(
                $endpoint,
                $this->buildCancelPayload(
                    $payment
                )
            );

            return [
                'success' =>
                    $result['success'],

                'message' =>
                    $result['message'],

                'data' =>
                    $result['data'],
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' =>
                    'Payment cancellation failed.',
                'data' => [],
            ];
        }
    }

    /**
     * Refund payment.
     */
    public function refundPayment(
        Payment $payment
    ): array {
        $endpoint = (string) (
            $this->config()['endpoints']['refund']
            ?? ''
        );

        if ($endpoint === '') {
            return [
                'success' => false,
                'message' =>
                    "{$this->gateway} refund endpoint is not configured.",
                'data' => [],
            ];
        }

        try {
            $result = $this->post(
                $endpoint,
                $this->buildRefundPayload(
                    $payment
                )
            );

            return [
                'success' =>
                    $result['success'],

                'message' =>
                    $result['message'],

                'data' =>
                    $result['data'],
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' =>
                    'Payment refund failed.',
                'data' => [],
            ];
        }
    }
}
