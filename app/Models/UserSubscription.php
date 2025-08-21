<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];

    protected $fillable = [
        'uid',
        'user_id',
        'subscription_id',
        'status',
        'expiration_date',
        'auto_renew',
        'auto_renew_date',
        'cancellation_date',
        'cancellation_reason',
        'payment_method_id',
        'transaction_id',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'auto_renew' => 'boolean',
        'auto_renew_date' => 'datetime',
        'cancellation_date' => 'datetime',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     */
    protected $with = ['type'];

    /**
     * Get the subscription for the user.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class, 'uid', 'subscription_id');
    }

    /**
     * Get the payment method for the subscription.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function paymentMethod(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PaymentMethod::class, 'uid', 'payment_method_id');
    }

    /**
     * Scope: only active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: canceled subscriptions
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->expiration_date && now()->gt($this->expiration_date);
    }
}
