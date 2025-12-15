<?php

namespace Modules\Admin\app\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Admin\app\Events\OnboardingRejected;

class SendOnboardingRejectedNotification
{
    public function handle(OnboardingRejected $event): void
    {
        $onboarding = $event->onboarding;
        $user = $onboarding->user;

        Log::info('Onboarding rejected notification', [
            'onboarding_id' => $onboarding->id,
            'user_id' => $user->id,
            'rejected_by' => $event->admin->id,
            'reason' => $event->reason,
        ]);

        // Here you can integrate with notification services
        // $user->notify(new OnboardingRejectedNotification($onboarding, $event->reason));
    }
}
