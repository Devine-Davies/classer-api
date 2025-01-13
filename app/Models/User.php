<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\AccountStatus;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'name',
        'email',
        'password',
        'email_verified_at',
        'email_verification_token',
        'password_reset_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
        'password_reset_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed'
    ];

    /**
     * Create a new User instance.
     * @param array<string, mixed> $attributes
     */
    function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get the subscription for the user.
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class, 'uid', 'uid');
    }

    /**
     * Account Verified
     */
    public function accountVerified(): bool
    {
        // return $this->account_status === AccountStatus::VERIFIED;
        return $this->account_status === 1;
    }

    /**
     * Account Inactive
     * @return bool
     */
    public function accountInactive(): bool
    {
        // return $this->account_status === AccountStatus::INACTIVE;
        return $this->account_status === 0;
    }

    /**
     * Account Deactivated
     * @return bool
     */
    public function accountDeactivated(): bool
    {
        // return $this->account_status === AccountStatus::DEACTIVATED;
        return $this->account_status === 2;
    }

    /**
     * Account Suspended
     * @return bool
     */
    public function accountSuspended(): bool
    {
        // var_dump($this->account_status);
        // return $this->account_status === AccountStatus::SUSPENDED;
        return $this->account_status === 3;
    }
}
