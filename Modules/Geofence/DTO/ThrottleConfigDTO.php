<?php

namespace Modules\Geofence\DTO;

class ThrottleConfigDTO
{
    public function __construct(
        public readonly int $maxPerDay,
        public readonly int $maxPerWeek,
        public readonly int $minIntervalMinutes,
        public readonly int $brandCooldownHours,
        public readonly int $locationCooldownHours,
        public readonly int $offerCooldownHours,
        public readonly bool $quietHoursEnabled,
        public readonly string $quietHoursStart,
        public readonly string $quietHoursEnd,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            maxPerDay: config('geofence.throttle.max_per_day', 5),
            maxPerWeek: config('geofence.throttle.max_per_week', 15),
            minIntervalMinutes: config('geofence.throttle.min_interval_minutes', 30),
            brandCooldownHours: config('geofence.throttle.brand_cooldown_hours', 24),
            locationCooldownHours: config('geofence.throttle.location_cooldown_hours', 4),
            offerCooldownHours: config('geofence.throttle.offer_cooldown_hours', 48),
            quietHoursEnabled: config('geofence.throttle.quiet_hours.enabled', true),
            quietHoursStart: config('geofence.throttle.quiet_hours.start', '22:00'),
            quietHoursEnd: config('geofence.throttle.quiet_hours.end', '08:00'),
        );
    }

    public function isQuietHours(): bool
    {
        if (!$this->quietHoursEnabled) {
            return false;
        }

        $now = now();
        $start = today()->setTimeFromTimeString($this->quietHoursStart);
        $end = today()->setTimeFromTimeString($this->quietHoursEnd);

        // Handle overnight quiet hours (e.g., 22:00 - 08:00)
        if ($start > $end) {
            return $now >= $start || $now < $end;
        }

        return $now >= $start && $now < $end;
    }
}
