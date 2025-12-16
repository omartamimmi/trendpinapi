<?php

namespace Modules\Geofence\Services;

use Modules\Geofence\Services\Contracts\RadarServiceInterface;
use Modules\Geofence\app\Models\Geofence;
use Modules\Geofence\Repositories\Contracts\GeofenceRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class RadarService implements RadarServiceInterface
{
    private string $baseUrl = 'https://api.radar.io/v1';
    private ?string $secretKey;
    private ?string $publishableKey;

    public function __construct(
        private GeofenceRepositoryInterface $geofenceRepository
    ) {
        // Load from database first, fall back to config
        $this->secretKey = $this->getSettingFromDb('radar_secret_key')
            ?? config('geofence.radar.secret_key', '');
        $this->publishableKey = $this->getSettingFromDb('radar_publishable_key')
            ?? config('geofence.radar.publishable_key', '');
    }

    /**
     * Get setting from database
     */
    private function getSettingFromDb(string $key): ?string
    {
        $value = \Illuminate\Support\Facades\DB::table('settings')
            ->where('key', "geofence.{$key}")
            ->value('value');

        return $value ?: null;
    }

    /**
     * Check if Radar.io is properly configured
     */
    private function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

    /**
     * Create a geofence in Radar.io
     */
    public function createGeofence(Geofence $geofence): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('Radar.io not configured, skipping geofence creation');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->secretKey,
            ])->post("{$this->baseUrl}/geofences", [
                'description' => $geofence->name,
                'tag' => $this->getGeofenceTag($geofence),
                'externalId' => $this->getExternalId($geofence),
                'type' => 'circle',
                'coordinates' => [(float)$geofence->lng, (float)$geofence->lat],
                'radius' => $geofence->radius,
                'enabled' => $geofence->is_active,
                'metadata' => [
                    'geofence_id' => $geofence->id,
                    'brand_id' => $geofence->brand_id,
                    'branch_id' => $geofence->branch_id,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['geofence']['_id'] ?? null;
            }

            Log::error('Radar geofence creation failed', [
                'geofence_id' => $geofence->id,
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Radar geofence creation exception', [
                'geofence_id' => $geofence->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update a geofence in Radar.io
     */
    public function updateGeofence(Geofence $geofence): bool
    {
        if (!$geofence->radar_geofence_id) {
            // Create if doesn't exist
            $radarId = $this->createGeofence($geofence);
            if ($radarId) {
                $this->geofenceRepository->update($geofence->id, [
                    'radar_geofence_id' => $radarId,
                ]);
                return true;
            }
            return false;
        }

        try {
            $tag = $this->getGeofenceTag($geofence);
            $externalId = $this->getExternalId($geofence);

            $response = Http::withHeaders([
                'Authorization' => $this->secretKey,
            ])->put("{$this->baseUrl}/geofences/{$tag}/{$externalId}", [
                'description' => $geofence->name,
                'coordinates' => [(float)$geofence->lng, (float)$geofence->lat],
                'radius' => $geofence->radius,
                'enabled' => $geofence->is_active,
                'metadata' => [
                    'geofence_id' => $geofence->id,
                    'brand_id' => $geofence->brand_id,
                    'branch_id' => $geofence->branch_id,
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Radar geofence update failed', [
                'geofence_id' => $geofence->id,
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Radar geofence update exception', [
                'geofence_id' => $geofence->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Delete a geofence from Radar.io
     */
    public function deleteGeofence(string $radarGeofenceId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->secretKey,
            ])->delete("{$this->baseUrl}/geofences/{$radarGeofenceId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Radar geofence deletion exception', [
                'radar_geofence_id' => $radarGeofenceId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Sync all local geofences to Radar.io
     */
    public function syncAllGeofences(): array
    {
        if (!$this->isConfigured()) {
            Log::warning('Radar.io not configured, skipping geofence sync');
            return ['created' => 0, 'updated' => 0, 'failed' => 0, 'skipped' => true];
        }

        $geofences = $this->geofenceRepository->getAllActive();
        $results = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
        ];

        foreach ($geofences as $geofence) {
            if (!$geofence->radar_geofence_id) {
                $radarId = $this->createGeofence($geofence);
                if ($radarId) {
                    $this->geofenceRepository->update($geofence->id, [
                        'radar_geofence_id' => $radarId,
                        'last_synced_at' => now(),
                    ]);
                    $results['created']++;
                } else {
                    $results['failed']++;
                }
            } else {
                if ($this->updateGeofence($geofence)) {
                    $this->geofenceRepository->update($geofence->id, [
                        'last_synced_at' => now(),
                    ]);
                    $results['updated']++;
                } else {
                    $results['failed']++;
                }
            }
        }

        return $results;
    }

    /**
     * Get all geofences from Radar.io
     */
    public function listGeofences(?string $tag = null): array
    {
        try {
            $url = "{$this->baseUrl}/geofences";
            if ($tag) {
                $url .= "?tag={$tag}";
            }

            $response = Http::withHeaders([
                'Authorization' => $this->secretKey,
            ])->get($url);

            if ($response->successful()) {
                return $response->json()['geofences'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Radar list geofences exception', [
                'tag' => $tag,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = $this->getSettingFromDb('radar_webhook_secret')
            ?? config('geofence.radar.webhook_secret');

        if (!$webhookSecret) {
            // If no secret configured, skip verification (not recommended for production)
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Create or update user in Radar.io
     */
    public function upsertUser(int $userId, array $metadata = []): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->secretKey,
            ])->put("{$this->baseUrl}/users/{$userId}", [
                'metadata' => array_merge($metadata, [
                    'user_id' => $userId,
                ]),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['user']['_id'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Radar user upsert exception', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get tag for geofence based on type
     */
    private function getGeofenceTag(Geofence $geofence): string
    {
        if ($geofence->branch_id) {
            return 'branch';
        }
        if ($geofence->brand_id) {
            return 'brand';
        }
        return 'custom';
    }

    /**
     * Get external ID for geofence
     */
    private function getExternalId(Geofence $geofence): string
    {
        if ($geofence->branch_id) {
            return "branch_{$geofence->branch_id}";
        }
        if ($geofence->brand_id) {
            return "brand_{$geofence->brand_id}";
        }
        return "geofence_{$geofence->id}";
    }
}
