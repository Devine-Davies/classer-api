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
        'uid',
        'key',
    ];

    protected $hidden = [
        'id',
        'uid'
    ];

    protected $casts = [
        'expiration_date' => 'datetime',
        'issue_date' => 'datetime',
    ];
}
