<?php

namespace Modules\Admin\app\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

class OnboardingRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public RetailerOnboarding $onboarding,
        public User $admin,
        public string $reason
    ) {}
}
