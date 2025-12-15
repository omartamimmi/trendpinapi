<?php

namespace Modules\Admin\app\Services\Contracts;

interface DashboardServiceInterface
{
    public function getStats(): array;

    public function getUserStats(): array;

    public function getOnboardingStats(): array;

    public function getSubscriptionStats(): array;

    public function getPaymentStats(): array;

    public function getPlanStats(): array;
}
