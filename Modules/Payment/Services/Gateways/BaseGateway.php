<?php

namespace Modules\Payment\Services\Gateways;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Payment\app\Models\PaymentSetting;
use Modules\Payment\Services\Contracts\PaymentGatewayInterface;

abstract class BaseGateway implements PaymentGatewayInterface
{
    protected ?PaymentSetting $settings = null;
    protected array $config = [];

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load settings from database and config
     */
    protected function loadSettings(): void
    {
        $this->settings = PaymentSetting::forGateway($this->getIdentifier());

        // Merge with config file settings
        $this->config = array_merge(
            config("payment.gateways.{$this->getIdentifier()}", []),
            $this->settings?->getDecryptedCredentials() ?? []
        );
    }

    /**
     * Check if gateway is enabled
     */
    public function isEnabled(): bool
    {
        return ($this->settings?->is_enabled ?? false) ||
            ($this->config['enabled'] ?? false);
    }

    /**
     * Check if in sandbox mode
     */
    protected function isSandbox(): bool
    {
        return $this->settings?->is_sandbox ??
            ($this->config['sandbox'] ?? true);
    }

    /**
     * Get base URL
     */
    protected function getBaseUrl(): string
    {
        return $this->config['base_url'] ?? '';
    }

    /**
     * Get credential value
     */
    protected function getCredential(string $key, $default = null)
    {
        return $this->settings?->getCredential($key) ??
            ($this->config[$key] ?? $default);
    }

    /**
     * Make HTTP request to gateway
     */
    protected function request(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        $url = $this->getBaseUrl() . ltrim($endpoint, '/');

        $defaultHeaders = $this->getDefaultHeaders();
        $headers = array_merge($defaultHeaders, $headers);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->{strtolower($method)}($url, $data);

            $body = $response->json() ?? [];

            $this->logRequest($method, $url, $data, $body, $response->status());

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $body,
            ];
        } catch (\Exception $e) {
            $this->logError($method, $url, $e);

            return [
                'success' => false,
                'status' => 0,
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Get default headers
     */
    abstract protected function getDefaultHeaders(): array;

    /**
     * Log API request
     */
    protected function logRequest(
        string $method,
        string $url,
        array $request,
        array $response,
        int $status
    ): void {
        // Sanitize sensitive data
        $sanitizedRequest = $this->sanitizeForLog($request);
        $sanitizedResponse = $this->sanitizeForLog($response);

        Log::channel('payment')->info("Payment Gateway Request", [
            'gateway' => $this->getIdentifier(),
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'request' => $sanitizedRequest,
            'response' => $sanitizedResponse,
        ]);
    }

    /**
     * Log API error
     */
    protected function logError(string $method, string $url, \Exception $e): void
    {
        Log::channel('payment')->error("Payment Gateway Error", [
            'gateway' => $this->getIdentifier(),
            'method' => $method,
            'url' => $url,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Sanitize data for logging (remove sensitive info)
     */
    protected function sanitizeForLog(array $data): array
    {
        $sensitiveKeys = [
            'card_number', 'card', 'cvv', 'cvc', 'security_code',
            'password', 'secret', 'token', 'authorization',
        ];

        return collect($data)->map(function ($value, $key) use ($sensitiveKeys) {
            if (is_array($value)) {
                return $this->sanitizeForLog($value);
            }

            foreach ($sensitiveKeys as $sensitive) {
                if (stripos($key, $sensitive) !== false) {
                    return '***REDACTED***';
                }
            }

            return $value;
        })->toArray();
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(): string
    {
        return strtoupper($this->getIdentifier()) . '-' .
            strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8)) . '-' .
            time();
    }
}
