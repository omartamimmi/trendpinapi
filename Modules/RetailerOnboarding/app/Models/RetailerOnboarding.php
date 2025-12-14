<?php

namespace Modules\RetailerOnboarding\app\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RetailerOnboarding extends Model
{
    protected $fillable = [
        'user_id',
        'current_step',
        'phone_verified',
        'cliq_verified',
        'completed_steps',
        'status',
        'requires_completion',
        'approval_status',
        'admin_notes',
        'approved_by',
        'approved_at',
        'city',
        'category',
        'logo_path',
        'license_path',
    ];

    protected $casts = [
        'phone_verified' => 'boolean',
        'cliq_verified' => 'boolean',
        'completed_steps' => 'array',
        'requires_completion' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markStepCompleted(string $step): void
    {
        $completed = $this->completed_steps ?? [];
        if (!in_array($step, $completed)) {
            $completed[] = $step;
        }
        $this->update(['completed_steps' => $completed]);
    }

    public function isStepCompleted(string $step): bool
    {
        return in_array($step, $this->completed_steps ?? []);
    }
}
