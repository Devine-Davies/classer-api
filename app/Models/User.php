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
     * Get the subscription for the user.
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class, 'uid', 'uid');
    }


    public function accountVerified(): bool
    {
        return $this->account_status === AccountStatus::VERIFIED;
    }

    /**
     * Account Inactive
     * @return bool
     */
    public function accountInactive(): bool
    {
        return $this->account_status === AccountStatus::INACTIVE;
    }

    /**
     * Account Deactivated
     * @return bool
     */
    public function accountDeactivated(): bool
    {
        return $this->account_status === AccountStatus::DEACTIVATED;
    }

    /**
     * Account Suspended
     * @return bool
     */
    public function accountSuspended(): bool
    {
        return $this->account_status === AccountStatus::SUSPENDED;
    }
}
