<?php

namespace Modules\Geofence\DTO;

class RadarEventDTO
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $type, // user.entered_geofence, user.exited_geofence, user.dwelled_in_geofence
        public readonly string $radarUserId,
        public readonly ?int $userId,
        public readonly ?string $geofenceId,
        public readonly ?string $externalId,
        public readonly ?string $tag,
        public readonly float $lat,
        public readonly float $lng,
        public readonly ?float $accuracy,
        public readonly array $metadata,
        public readonly string $occurredAt,
    ) {}

    public static function fromWebhook(array $data): self
    {
        $event = $data['event'] ?? $data;
        $user = $event['user'] ?? [];
        $geofence = $event['geofence'] ?? [];
        $location = $event['location'] ?? $user['location'] ?? [];

        // Extract user ID from metadata or user object
        $userId = $user['metadata']['user_id'] 
            ?? $user['userId'] 
            ?? null;

        return new self(
            eventId: $event['_id'] ?? $data['_id'] ?? uniqid('radar_'),
            type: $event['type'] ?? $data['type'] ?? 'unknown',
            radarUserId: $user['_id'] ?? $user['userId'] ?? '',
            userId: $userId ? (int) $userId : null,
            geofenceId: $geofence['_id'] ?? null,
            externalId: $geofence['externalId'] ?? null,
            tag: $geofence['tag'] ?? null,
            lat: (float) ($location['coordinates'][1] ?? $location['latitude'] ?? 0),
            lng: (float) ($location['coordinates'][0] ?? $location['longitude'] ?? 0),
            accuracy: isset($location['accuracy']) ? (float) $location['accuracy'] : null,
            metadata: array_merge(
                $geofence['metadata'] ?? [],
                $user['metadata'] ?? []
            ),
            occurredAt: $event['createdAt'] ?? $data['createdAt'] ?? now()->toIso8601String(),
        );
    }

    public function isGeofenceEntry(): bool
    {
        return str_contains($this->type, 'entered_geofence');
    }

    public function isGeofenceExit(): bool
    {
        return str_contains($this->type, 'exited_geofence');
    }

    public function isGeofenceDwell(): bool
    {
        return str_contains($this->type, 'dwelled_in_geofence');
    }

    public function getEventType(): string
    {
        if ($this->isGeofenceEntry()) return 'entry';
        if ($this->isGeofenceExit()) return 'exit';
        if ($this->isGeofenceDwell()) return 'dwell';
        return 'unknown';
    }

    public function getBranchId(): ?int
    {
        // Try to extract from externalId first (format: branch_123)
        if ($this->externalId && str_starts_with($this->externalId, 'branch_')) {
            return (int) str_replace('branch_', '', $this->externalId);
        }

        // Try from metadata
        return $this->metadata['branch_id'] ?? null;
    }

    public function getBrandId(): ?int
    {
        // Try to extract from externalId first (format: brand_123)
        if ($this->externalId && str_starts_with($this->externalId, 'brand_')) {
            return (int) str_replace('brand_', '', $this->externalId);
        }

        // Try from metadata
        return $this->metadata['brand_id'] ?? null;
    }

    public function getGeofenceDbId(): ?int
    {
        return $this->metadata['geofence_id'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'type' => $this->type,
            'radar_user_id' => $this->radarUserId,
            'user_id' => $this->userId,
            'geofence_id' => $this->geofenceId,
            'external_id' => $this->externalId,
            'tag' => $this->tag,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'accuracy' => $this->accuracy,
            'metadata' => $this->metadata,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
