<?php

namespace Modules\Admin\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'price' => (float) $this->price,
            'formatted_price' => number_format($this->price, 2),
            'offers_count' => (int) $this->offers_count,
            'duration_months' => (int) $this->duration_months,
            'billing_period' => $this->billing_period ?? 'monthly',
            'trial_days' => (int) ($this->trial_days ?? 0),
            'color' => $this->color,
            'is_active' => (bool) $this->is_active,
            'subscribers_count' => $this->whenCounted('subscriptions'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
