<?php

namespace Modules\Admin\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'users' => [
                'total' => $this->resource['total_users'] ?? 0,
                'this_month' => $this->resource['users_this_month'] ?? 0,
                'growth_percentage' => $this->resource['user_growth'] ?? 0,
            ],
            'retailers' => [
                'total' => $this->resource['total_retailers'] ?? 0,
                'pending_approval' => $this->resource['pending_retailers'] ?? 0,
            ],
            'plans' => [
                'total' => $this->resource['total_plans'] ?? 0,
                'active' => $this->resource['active_plans'] ?? 0,
            ],
            'subscriptions' => [
                'total' => $this->resource['total_subscriptions'] ?? 0,
                'active' => $this->resource['active_subscriptions'] ?? 0,
            ],
            'revenue' => [
                'total' => $this->resource['total_revenue'] ?? 0,
                'this_month' => $this->resource['revenue_this_month'] ?? 0,
                'formatted_total' => number_format($this->resource['total_revenue'] ?? 0, 2),
                'formatted_this_month' => number_format($this->resource['revenue_this_month'] ?? 0, 2),
            ],
            'onboarding' => [
                'pending' => $this->resource['pending_onboarding'] ?? 0,
                'approved_this_month' => $this->resource['approved_this_month'] ?? 0,
            ],
        ];
    }
}
