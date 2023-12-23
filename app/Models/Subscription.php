<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Subscription extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uid',
        'sub_type',
        'status',
        'issue_date',
        'expiration_date',
        'renewal_fee'
    ];

    protected $hidden = [
        'id',
        'uid',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'limit_short_count' => 'integer',
        'limit_short_duration' => 'integer',
        'limit_short_size' => 'integer',
        'expiration_date' => 'datetime',
        'issue_date' => 'datetime',
    ];

    protected $defaults = [
        'status' => 1,
    ];

    /**
     * Get the subscription for the user.
     */
    public function type(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SubscriptionType::class, 'code', 'sub_type');
    }
}
