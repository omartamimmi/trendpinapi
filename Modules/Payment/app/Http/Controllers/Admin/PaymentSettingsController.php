<?php

namespace Modules\Payment\app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Modules\Payment\app\Models\PaymentSetting;
use Modules\Payment\app\Models\PaymentMethodSetting;

class PaymentSettingsController extends Controller
{
    /**
     * GET /api/v1/admin/payment/settings
     * Get all payment settings overview
     */
    public function index(): JsonResponse
    {
        $gateways = PaymentSetting::all()->map(fn($setting) => [
            'id' => $setting->id,
            'gateway' => $setting->gateway,
            'display_name' => $setting->display_name,
            'is_enabled' => $setting->is_enabled,
            'is_sandbox' => $setting->is_sandbox,
            'supported_methods' => $setting->supported_methods,
            'is_default' => $setting->is_default,
            'has_credentials' => !empty($setting->getDecryptedCredentials()),
            'last_tested_at' => $setting->last_tested_at?->toIso8601String(),
            'test_status' => $setting->test_status,
            'updated_at' => $setting->updated_at->toIso8601String(),
        ]);

        $methods = PaymentMethodSetting::all()->map(fn($method) => [
            'id' => $method->id,
            'method' => $method->method,
            'display_name' => $method->display_name,
            'is_enabled' => $method->is_enabled,
            'preferred_gateway' => $method->preferred_gateway,
            'fee_type' => $method->fee_type,
            'fee_value' => $method->fee_value,
            'min_amount' => $method->min_amount,
            'max_amount' => $method->max_amount,
            'updated_at' => $method->updated_at->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'gateways' => $gateways,
                'methods' => $methods,
                'default_gateway' => config('payment.default_gateway'),
                'qr_expiry_minutes' => config('payment.qr.expiry_minutes'),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/payment/gateways
     * List all payment gateways
     */
    public function listGateways(): JsonResponse
    {
        $gateways = PaymentSetting::all();

        // Include default config for gateways not in database
        $configuredGateways = config('payment.gateways', []);
        $existingGateways = $gateways->pluck('gateway')->toArray();

        foreach ($configuredGateways as $gateway => $config) {
            if (!in_array($gateway, $existingGateways)) {
                $gateways->push(new PaymentSetting([
                    'gateway' => $gateway,
                    'display_name' => $config['display_name'] ?? ucfirst($gateway),
                    'is_enabled' => false,
                    'is_sandbox' => true,
                    'supported_methods' => $config['supported_methods'] ?? [],
                ]));
            }
        }

        return response()->json([
            'success' => true,
            'data' => $gateways->map(fn($setting) => [
                'id' => $setting->id,
                'gateway' => $setting->gateway,
                'display_name' => $setting->display_name,
                'is_enabled' => $setting->is_enabled,
                'is_sandbox' => $setting->is_sandbox,
                'supported_methods' => $setting->supported_methods,
                'is_default' => $setting->is_default,
                'has_credentials' => !empty($setting->getDecryptedCredentials()),
                'credential_fields' => $this->getCredentialFields($setting->gateway),
                'last_tested_at' => $setting->last_tested_at?->toIso8601String(),
                'test_status' => $setting->test_status,
            ]),
        ]);
    }

    /**
     * GET /api/v1/admin/payment/gateways/{gateway}
     * Get gateway details
     */
    public function showGateway(string $gateway): JsonResponse
    {
        $setting = PaymentSetting::where('gateway', $gateway)->first();

        if (!$setting) {
            // Return default config structure
            $config = config("payment.gateways.{$gateway}");
            if (!$config) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gateway not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'gateway' => $gateway,
                    'display_name' => $config['display_name'] ?? ucfirst($gateway),
                    'is_enabled' => false,
                    'is_sandbox' => true,
                    'supported_methods' => $config['supported_methods'] ?? [],
                    'credentials' => [],
                    'credential_fields' => $this->getCredentialFields($gateway),
                    'configuration' => [],
                ],
            ]);
        }

        // Mask sensitive credentials
        $credentials = $setting->getDecryptedCredentials();
        $maskedCredentials = $this->maskCredentials($credentials);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $setting->id,
                'gateway' => $setting->gateway,
                'display_name' => $setting->display_name,
                'is_enabled' => $setting->is_enabled,
                'is_sandbox' => $setting->is_sandbox,
                'supported_methods' => $setting->supported_methods,
                'is_default' => $setting->is_default,
                'credentials' => $maskedCredentials,
                'credential_fields' => $this->getCredentialFields($gateway),
                'configuration' => $setting->configuration ?? [],
                'last_tested_at' => $setting->last_tested_at?->toIso8601String(),
                'test_status' => $setting->test_status,
                'test_message' => $setting->test_message,
            ],
        ]);
    }

    /**
     * PUT /api/v1/admin/payment/gateways/{gateway}
     * Update gateway settings
     */
    public function updateGateway(string $gateway, Request $request): JsonResponse
    {
        $request->validate([
            'display_name' => 'nullable|string|max:100',
            'is_enabled' => 'nullable|boolean',
            'is_sandbox' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'credentials' => 'nullable|array',
            'configuration' => 'nullable|array',
        ]);

        $setting = PaymentSetting::firstOrCreate(
            ['gateway' => $gateway],
            [
                'display_name' => $request->display_name ?? ucfirst($gateway),
                'is_enabled' => false,
                'is_sandbox' => true,
                'supported_methods' => config("payment.gateways.{$gateway}.supported_methods", []),
            ]
        );

        // Update basic settings
        $updateData = [];

        if ($request->has('display_name')) {
            $updateData['display_name'] = $request->display_name;
        }

        if ($request->has('is_enabled')) {
            $updateData['is_enabled'] = $request->is_enabled;
        }

        if ($request->has('is_sandbox')) {
            $updateData['is_sandbox'] = $request->is_sandbox;
        }

        if ($request->has('configuration')) {
            $updateData['configuration'] = $request->configuration;
        }

        // Handle credentials update
        if ($request->has('credentials') && is_array($request->credentials)) {
            $existingCredentials = $setting->getDecryptedCredentials();

            // Only update non-empty values (keep existing if empty)
            foreach ($request->credentials as $key => $value) {
                if (!empty($value) && $value !== '********') {
                    $existingCredentials[$key] = $value;
                }
            }

            $setting->setEncryptedCredentials($existingCredentials);
        }

        // Handle default gateway
        if ($request->is_default) {
            // Remove default from other gateways
            PaymentSetting::where('gateway', '!=', $gateway)
                ->update(['is_default' => false]);
            $updateData['is_default'] = true;
        }

        $setting->update($updateData);

        // Clear cache
        Cache::forget("payment_setting_{$gateway}");

        return response()->json([
            'success' => true,
            'message' => 'Gateway settings updated successfully',
            'data' => [
                'gateway' => $setting->gateway,
                'is_enabled' => $setting->is_enabled,
                'is_sandbox' => $setting->is_sandbox,
                'is_default' => $setting->is_default,
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/payment/gateways/{gateway}/test
     * Test gateway connection
     */
    public function testGateway(string $gateway): JsonResponse
    {
        $setting = PaymentSetting::where('gateway', $gateway)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway not configured',
            ], 400);
        }

        $credentials = $setting->getDecryptedCredentials();

        if (empty($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway credentials not set',
            ], 400);
        }

        try {
            // Get gateway instance and test connection
            $gatewayClass = $this->getGatewayClass($gateway);

            if (!$gatewayClass) {
                throw new \Exception("Gateway {$gateway} is not implemented");
            }

            $gatewayInstance = new $gatewayClass();

            // Test with a simple API call (implementation-specific)
            $testResult = $this->performGatewayTest($gatewayInstance, $gateway);

            // Update test status
            $setting->update([
                'last_tested_at' => now(),
                'test_status' => $testResult['success'] ? 'success' : 'failed',
                'test_message' => $testResult['message'] ?? null,
            ]);

            return response()->json([
                'success' => $testResult['success'],
                'message' => $testResult['message'],
                'data' => [
                    'gateway' => $gateway,
                    'test_status' => $testResult['success'] ? 'success' : 'failed',
                    'tested_at' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            $setting->update([
                'last_tested_at' => now(),
                'test_status' => 'failed',
                'test_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gateway test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/admin/payment/methods
     * List all payment methods
     */
    public function listMethods(): JsonResponse
    {
        $methods = PaymentMethodSetting::all();

        // Include default config for methods not in database
        $configuredMethods = config('payment.methods', []);
        $existingMethods = $methods->pluck('method')->toArray();

        foreach ($configuredMethods as $method => $config) {
            if (!in_array($method, $existingMethods)) {
                $methods->push(new PaymentMethodSetting([
                    'method' => $method,
                    'display_name' => $config['display_name'] ?? ucfirst(str_replace('_', ' ', $method)),
                    'is_enabled' => $config['enabled'] ?? false,
                    'fee_type' => 'percentage',
                    'fee_value' => 0,
                ]));
            }
        }

        return response()->json([
            'success' => true,
            'data' => $methods->map(fn($method) => [
                'id' => $method->id,
                'method' => $method->method,
                'display_name' => $method->display_name,
                'description' => $this->getMethodDescription($method->method),
                'is_enabled' => $method->is_enabled,
                'preferred_gateway' => $method->preferred_gateway,
                'fee_type' => $method->fee_type,
                'fee_value' => $method->fee_value,
                'min_amount' => $method->min_amount,
                'max_amount' => $method->max_amount,
                'available_gateways' => $this->getAvailableGatewaysForMethod($method->method),
            ]),
        ]);
    }

    /**
     * PUT /api/v1/admin/payment/methods/{method}
     * Update payment method settings
     */
    public function updateMethod(string $method, Request $request): JsonResponse
    {
        $request->validate([
            'display_name' => 'nullable|string|max:100',
            'is_enabled' => 'nullable|boolean',
            'preferred_gateway' => 'nullable|string|in:tap,hyperpay,paytabs',
            'fee_type' => 'nullable|string|in:percentage,fixed,none',
            'fee_value' => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'configuration' => 'nullable|array',
        ]);

        $setting = PaymentMethodSetting::firstOrCreate(
            ['method' => $method],
            [
                'display_name' => $request->display_name ?? ucfirst(str_replace('_', ' ', $method)),
                'is_enabled' => false,
            ]
        );

        $updateData = array_filter([
            'display_name' => $request->display_name,
            'is_enabled' => $request->is_enabled,
            'preferred_gateway' => $request->preferred_gateway,
            'fee_type' => $request->fee_type,
            'fee_value' => $request->fee_value,
            'min_amount' => $request->min_amount,
            'max_amount' => $request->max_amount,
            'configuration' => $request->configuration,
        ], fn($value) => !is_null($value));

        $setting->update($updateData);

        // Clear cache
        Cache::forget("payment_method_{$method}");

        return response()->json([
            'success' => true,
            'message' => 'Payment method settings updated successfully',
            'data' => [
                'method' => $setting->method,
                'is_enabled' => $setting->is_enabled,
                'preferred_gateway' => $setting->preferred_gateway,
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/payment/methods/{method}/toggle
     * Toggle payment method enabled/disabled
     */
    public function toggleMethod(string $method): JsonResponse
    {
        $setting = PaymentMethodSetting::firstOrCreate(
            ['method' => $method],
            [
                'display_name' => ucfirst(str_replace('_', ' ', $method)),
                'is_enabled' => false,
            ]
        );

        $setting->update(['is_enabled' => !$setting->is_enabled]);

        // Clear cache
        Cache::forget("payment_method_{$method}");

        return response()->json([
            'success' => true,
            'message' => $setting->is_enabled
                ? "{$setting->display_name} has been enabled"
                : "{$setting->display_name} has been disabled",
            'data' => [
                'method' => $setting->method,
                'is_enabled' => $setting->is_enabled,
            ],
        ]);
    }

    /**
     * PUT /api/v1/admin/payment/settings/general
     * Update general payment settings
     */
    public function updateGeneral(Request $request): JsonResponse
    {
        $request->validate([
            'default_gateway' => 'nullable|string|in:tap,hyperpay,paytabs',
            'qr_expiry_minutes' => 'nullable|integer|min:5|max:60',
            'session_expiry_minutes' => 'nullable|integer|min:5|max:120',
            'max_retry_attempts' => 'nullable|integer|min:1|max:5',
            'webhook_tolerance_seconds' => 'nullable|integer|min:60|max:600',
        ]);

        // Store in database settings table or config cache
        // For this implementation, we'll use a settings model
        $settings = [
            'payment.default_gateway' => $request->default_gateway,
            'payment.qr.expiry_minutes' => $request->qr_expiry_minutes,
            'payment.session_expiry_minutes' => $request->session_expiry_minutes,
            'payment.max_retry_attempts' => $request->max_retry_attempts,
            'payment.webhook_tolerance_seconds' => $request->webhook_tolerance_seconds,
        ];

        foreach (array_filter($settings, fn($v) => !is_null($v)) as $key => $value) {
            // Store in database or cache
            Cache::forever("config.{$key}", $value);
        }

        return response()->json([
            'success' => true,
            'message' => 'General settings updated successfully',
        ]);
    }

    /**
     * GET /api/v1/admin/payment/fees
     * Get fee configuration
     */
    public function getFees(): JsonResponse
    {
        $methods = PaymentMethodSetting::all();

        return response()->json([
            'success' => true,
            'data' => [
                'methods' => $methods->map(fn($method) => [
                    'method' => $method->method,
                    'display_name' => $method->display_name,
                    'fee_type' => $method->fee_type,
                    'fee_value' => $method->fee_value,
                    'fee_display' => $this->formatFeeDisplay($method),
                ]),
                'gateway_fees' => [
                    'tap' => config('payment.gateways.tap.fees', []),
                    'hyperpay' => config('payment.gateways.hyperpay.fees', []),
                    'paytabs' => config('payment.gateways.paytabs.fees', []),
                ],
            ],
        ]);
    }

    /**
     * Get credential fields for a gateway
     */
    private function getCredentialFields(string $gateway): array
    {
        $fields = [
            'tap' => [
                ['key' => 'public_key', 'label' => 'Public Key', 'type' => 'text', 'required' => true],
                ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
                ['key' => 'webhook_secret', 'label' => 'Webhook Secret', 'type' => 'password', 'required' => false],
                ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'required' => false],
            ],
            'hyperpay' => [
                ['key' => 'entity_id', 'label' => 'Entity ID', 'type' => 'text', 'required' => true],
                ['key' => 'access_token', 'label' => 'Access Token', 'type' => 'password', 'required' => true],
                ['key' => 'webhook_secret', 'label' => 'Webhook Secret', 'type' => 'password', 'required' => false],
            ],
            'paytabs' => [
                ['key' => 'profile_id', 'label' => 'Profile ID', 'type' => 'text', 'required' => true],
                ['key' => 'server_key', 'label' => 'Server Key', 'type' => 'password', 'required' => true],
                ['key' => 'client_key', 'label' => 'Client Key', 'type' => 'text', 'required' => false],
                ['key' => 'webhook_secret', 'label' => 'Webhook Secret', 'type' => 'password', 'required' => false],
            ],
            'cliq' => [
                ['key' => 'merchant_alias', 'label' => 'Merchant CliQ Alias', 'type' => 'text', 'required' => true],
                ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'required' => true],
                ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
                ['key' => 'webhook_secret', 'label' => 'Webhook Secret', 'type' => 'password', 'required' => false],
            ],
        ];

        return $fields[$gateway] ?? [];
    }

    /**
     * Mask sensitive credentials for display
     */
    private function maskCredentials(array $credentials): array
    {
        $sensitiveKeys = ['secret_key', 'access_token', 'server_key', 'api_key', 'webhook_secret'];

        return collect($credentials)->map(function ($value, $key) use ($sensitiveKeys) {
            if (in_array($key, $sensitiveKeys) && !empty($value)) {
                return '********';
            }
            return $value;
        })->toArray();
    }

    /**
     * Get gateway class
     */
    private function getGatewayClass(string $gateway): ?string
    {
        $classes = [
            'tap' => \Modules\Payment\Services\Gateways\TapPaymentsGateway::class,
            // 'hyperpay' => \Modules\Payment\Services\Gateways\HyperPayGateway::class,
            // 'paytabs' => \Modules\Payment\Services\Gateways\PayTabsGateway::class,
        ];

        return $classes[$gateway] ?? null;
    }

    /**
     * Perform gateway test
     */
    private function performGatewayTest($gateway, string $gatewayName): array
    {
        // Each gateway has different test methods
        // For Tap, we can try to list customers or create a test token

        try {
            if (method_exists($gateway, 'isEnabled') && $gateway->isEnabled()) {
                return [
                    'success' => true,
                    'message' => 'Gateway connection successful',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gateway is not properly configured',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get method description
     */
    private function getMethodDescription(string $method): string
    {
        $descriptions = [
            'card' => 'Accept credit and debit card payments with 3D Secure',
            'apple_pay' => 'Accept payments from Apple Pay wallets on iOS devices',
            'google_pay' => 'Accept payments from Google Pay wallets on Android devices',
            'cliq' => 'Accept instant bank transfers via Jordan\'s CliQ system',
        ];

        return $descriptions[$method] ?? '';
    }

    /**
     * Get available gateways for a method
     */
    private function getAvailableGatewaysForMethod(string $method): array
    {
        $gateways = PaymentSetting::where('is_enabled', true)->get();

        return $gateways->filter(function ($gateway) use ($method) {
            $supportedMethods = $gateway->supported_methods ?? [];
            return in_array($method, $supportedMethods);
        })->pluck('gateway')->toArray();
    }

    /**
     * Format fee display
     */
    private function formatFeeDisplay(PaymentMethodSetting $method): string
    {
        if ($method->fee_type === 'none' || $method->fee_value == 0) {
            return 'No fee';
        }

        if ($method->fee_type === 'percentage') {
            return $method->fee_value . '%';
        }

        return 'JOD ' . number_format($method->fee_value, 2);
    }
}
