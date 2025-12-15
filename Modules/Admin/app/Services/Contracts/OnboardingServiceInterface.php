<?php

namespace Modules\Admin\app\Services\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

interface OnboardingServiceInterface
{
    public function getOnboardings(string $status, ?string $search = null): LengthAwarePaginator;

    public function getOnboarding(int $id): RetailerOnboarding;

    public function getOnboardingDetails(int $id): array;

    public function getCounts(): array;

    public function approve(int $id, ?string $notes = null): RetailerOnboarding;

    public function requestChanges(int $id, string $notes): RetailerOnboarding;

    public function reject(int $id, string $notes): RetailerOnboarding;
}
