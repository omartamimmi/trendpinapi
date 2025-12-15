<?php

namespace Modules\RetailerOnboarding\app\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Modules\Media\Traits\HasMedia;

class RetailerOnboarding extends Model
{
    use HasMedia;

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

    protected $appends = ['logo_url', 'license_url'];

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

    /**
     * Get logo URL - prefers Media module, falls back to legacy path
     */
    public function getLogoUrlAttribute(): ?string
    {
        // First check for media relationship
        $logo = $this->getFirstMedia('logo');
        if ($logo) {
            return $logo->getPresetUrl('thumb');
        }

        // Fallback to legacy logo_path field
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }

        return null;
    }

    /**
     * Get license URL - prefers Media module, falls back to legacy path
     */
    public function getLicenseUrlAttribute(): ?string
    {
        // First check for media relationship
        $license = $this->getFirstMedia('license');
        if ($license) {
            return $license->url;
        }

        // Fallback to legacy license_path field
        if ($this->license_path) {
            return asset('storage/' . $this->license_path);
        }

        return null;
    }
}
