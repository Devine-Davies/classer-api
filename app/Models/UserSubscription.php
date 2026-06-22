<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];

    protected $fillable = [
        'uid',
        'user_id',
        'plan_id',
        'order_id',
        'status',
        'expiration_date',
        'cancellation_date',
        'cancellation_reason',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'cancellation_date' => 'datetime',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     */
    protected $with = ['plan'];

    /**
     * Get the linked plan for this user subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'uid');
    }

    /**
     * Get the user who owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uid');
    }

    /**
     * Get the order associated with this subscription.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'uid');
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
