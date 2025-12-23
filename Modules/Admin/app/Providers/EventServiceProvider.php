<?php

namespace Modules\Admin\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Admin\app\Events\AdminLoggedIn;
use Modules\Admin\app\Events\OnboardingApproved;
use Modules\Admin\app\Events\OnboardingRejected;
use Modules\Admin\app\Listeners\LogAdminActivity;
use Modules\Admin\app\Listeners\SendOnboardingApprovedNotification;
use Modules\Admin\app\Listeners\SendOnboardingRejectedNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        AdminLoggedIn::class => [
            LogAdminActivity::class,
        ],
        OnboardingApproved::class => [
            SendOnboardingApprovedNotification::class,
        ],
        OnboardingRejected::class => [
            SendOnboardingRejectedNotification::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
