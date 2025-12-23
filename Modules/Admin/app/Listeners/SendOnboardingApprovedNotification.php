<?php

namespace Modules\Admin\app\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Admin\app\Events\OnboardingApproved;

class SendOnboardingApprovedNotification
{
    public function handle(OnboardingApproved $event): void
    {
        $onboarding = $event->onboarding;
        $user = $onboarding->user;

        Log::info('Onboarding approved notification', [
            'onboarding_id' => $onboarding->id,
            'user_id' => $user->id,
            'approved_by' => $event->approver->id,
        ]);

        // Here you can integrate with notification services
        // $user->notify(new OnboardingApprovedNotification($onboarding));
    }
}
