<?php

namespace Modules\RetailerOnboarding\app\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'retailer_subscription_id',
        'user_id',
        'amount',
        'discount',
        'subtotal',
        'total',
        'discount_code',
        'payment_method',
        'status',
        'card_last_four',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function subscription()
    {
        return $this->belongsTo(RetailerSubscription::class, 'retailer_subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
