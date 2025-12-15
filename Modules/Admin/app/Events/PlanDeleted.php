<?php

namespace Modules\Admin\app\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\RetailerOnboarding\app\Models\SubscriptionPlan;

class PlanDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public SubscriptionPlan $plan
    ) {}
}
