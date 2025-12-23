<?php

namespace Modules\Admin\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnboardingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'website' => $this->website,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'current_step' => (int) $this->current_step,
            'total_steps' => 4,
            'progress_percentage' => $this->getProgressPercentage(),
            'documents' => $this->whenLoaded('documents'),
            'admin_notes' => $this->admin_notes,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toISOString(),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    protected function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'changes_requested' => 'Changes Requested',
            'rejected' => 'Rejected',
            default => ucfirst($this->status ?? 'unknown'),
        };
    }

    protected function getProgressPercentage(): int
    {
        $totalSteps = 4;
        return (int) (($this->current_step / $totalSteps) * 100);
    }
}
