<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SubscriptionType extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'key',
        'label',
        'type',
        'limit_short_count',
        'limit_short_duration',
        'limit_short_size',
    ];

    protected $hidden = [
        'id',
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
}
