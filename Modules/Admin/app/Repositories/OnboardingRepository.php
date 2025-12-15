<?php

namespace Modules\Admin\app\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Admin\app\Repositories\Contracts\OnboardingRepositoryInterface;
use Modules\RetailerOnboarding\app\Models\RetailerOnboarding;

class OnboardingRepository extends BaseRepository implements OnboardingRepositoryInterface
{
    public function __construct(RetailerOnboarding $model)
    {
        parent::__construct($model);
    }

    public function getByStatus(string $status): Collection
    {
        return $this->model->where('approval_status', $status)->get();
    }

    public function paginateByStatus(string $status, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->model->with(['user']);

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function findWithRelations(int $id): RetailerOnboarding
    {
        return $this->model->with(['user', 'approver'])->findOrFail($id);
    }

    public function getStatusCounts(): array
    {
        $statuses = config('admin.onboarding_statuses', [
            'pending', 'pending_approval', 'approved', 'changes_requested', 'rejected'
        ]);

        $counts = [];
        foreach ($statuses as $status) {
            $counts[$status] = $this->countByStatus($status);
        }

        return $counts;
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('approval_status', $status)->count();
    }

    public function countTotal(): int
    {
        return $this->model->count();
    }

    public function countInProgress(): int
    {
        return $this->model->where('status', 'in_progress')->count();
    }

    public function countCompleted(): int
    {
        return $this->model->where('status', 'completed')->count();
    }

    public function approve(int $id, int $approvedBy, ?string $notes = null): RetailerOnboarding
    {
        $onboarding = $this->findOrFail($id);
        $onboarding->update([
            'approval_status' => 'approved',
            'admin_notes' => $notes,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $onboarding->fresh();
    }

    public function requestChanges(int $id, int $approvedBy, string $notes): RetailerOnboarding
    {
        $onboarding = $this->findOrFail($id);
        $onboarding->update([
            'approval_status' => 'changes_requested',
            'admin_notes' => $notes,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $onboarding->fresh();
    }

    public function reject(int $id, int $approvedBy, string $notes): RetailerOnboarding
    {
        $onboarding = $this->findOrFail($id);
        $onboarding->update([
            'approval_status' => 'rejected',
            'admin_notes' => $notes,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);

        return $onboarding->fresh();
    }
}
