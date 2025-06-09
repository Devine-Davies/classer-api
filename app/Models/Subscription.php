<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Subscription extends Authenticatable
{
    protected $fillable = [
        'uid',
        'code',
        'quota',
    ];

    protected $hidden = [
        'id',
    ];

    protected $casts = [
        'quota' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
