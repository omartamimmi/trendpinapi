<?php

namespace Modules\Geofence\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Geofence\Services\Contracts\RadarServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyRadarSignature
{
    public function __construct(
        private RadarServiceInterface $radarService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification in local/testing environments if configured
        if (config('geofence.radar.skip_signature_verification', false)) {
            return $next($request);
        }

        $signature = $request->header('X-Radar-Signature');

        // If no signature header, try alternative header names
        if (!$signature) {
            $signature = $request->header('Radar-Signature');
        }

        $payload = $request->getContent();

        // Log for debugging
        Log::debug('Verifying Radar webhook signature', [
            'has_signature' => !empty($signature),
            'payload_length' => strlen($payload),
        ]);

        if (!$this->radarService->verifyWebhookSignature($payload, $signature ?? '')) {
            Log::warning('Invalid Radar webhook signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Invalid signature',
            ], 401);
        }

        return $next($request);
    }
}
