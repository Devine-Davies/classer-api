<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * UserSubscription Model
 *
 * Represents a user's subscription details.
 */
class UserSubscription extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'user_id',
        'subscription_id',
        'expiration_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'uid',
        'subscription_id',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'expiration_date' => 'datetime',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     */
    protected $with = ['tier'];

    /**
     * Get the subscription for the user.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tier(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Subscription::class, 'uid', 'subscription_id');
    }
}
