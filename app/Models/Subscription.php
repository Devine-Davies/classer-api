<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

class Subscription extends Authenticatable
{
    // use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uid',
        'code',
        'quota',
    ];

    protected $hidden = [
        'id',
        'uid',
    ];

    protected $casts = [
        'quota' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $defaults = [
        // 'status' => 1,
    ];

    // /**
    //  * Get the subscription for the user.
    //  */
    // public function type(): \Illuminate\Database\Eloquent\Relations\HasOne
    // {
    //     return $this->hasOne(SubscriptionType::class, 'code', 'sub_type');
    // }
}
