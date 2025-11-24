<?php

namespace Modules\RetailerOnboarding\app\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class RetailerPaymentMethod extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'cliq_number',
        'cliq_verified',
        'bank_name',
        'iban',
        'is_primary',
    ];

    protected $casts = [
        'cliq_verified' => 'boolean',
        'is_primary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCliq($query)
    {
        return $query->where('type', 'cliq');
    }

    public function scopeBank($query)
    {
        return $query->where('type', 'bank');
    }
}
