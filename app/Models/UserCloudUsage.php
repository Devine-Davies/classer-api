<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * UserCloudUsage Model
 *
 * Represents the cloud usage details for a user.
 */
class UserCloudUsage extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uid',
        'total',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'id',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total' => 'integer',
        'updated_at' => 'datetime',
    ];
}
