<?php

namespace Modules\Log\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this->resource['summary'],
            'by_level' => $this->formatLevelCounts($this->resource['by_level']),
            'by_channel' => $this->formatChannelCounts($this->resource['by_channel']),
            'top_exceptions' => $this->formatExceptions($this->resource['top_exceptions']),
            'timeline' => $this->formatTimeline($this->resource['timeline']),
        ];
    }

    protected function formatLevelCounts(array $levels): array
    {
        $colors = [
            'emergency' => '#dc2626',
            'alert' => '#dc2626',
            'critical' => '#dc2626',
            'error' => '#ea580c',
            'warning' => '#ca8a04',
            'notice' => '#2563eb',
            'info' => '#16a34a',
            'debug' => '#6b7280',
        ];

        $formatted = [];
        foreach ($levels as $level => $count) {
            $formatted[] = [
                'level' => $level,
                'count' => $count,
                'color' => $colors[$level] ?? '#6b7280',
            ];
        }

        return $formatted;
    }

    protected function formatChannelCounts(array $channels): array
    {
        $formatted = [];
        foreach ($channels as $channel => $count) {
            $formatted[] = [
                'channel' => $channel,
                'count' => $count,
            ];
        }

        return $formatted;
    }

    protected function formatExceptions($exceptions): array
    {
        return $exceptions->map(function ($exception) {
            return [
                'class' => $exception->exception_class,
                'message' => $exception->exception_message,
                'count' => $exception->count,
                'last_occurrence' => $exception->last_occurrence,
            ];
        })->toArray();
    }

    protected function formatTimeline($timeline): array
    {
        $grouped = $timeline->groupBy('time_bucket');

        return $grouped->map(function ($items, $timeBucket) {
            $data = ['time' => $timeBucket];
            foreach ($items as $item) {
                $data[$item->level] = $item->count;
            }
            return $data;
        })->values()->toArray();
    }
}
