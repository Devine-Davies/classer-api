<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserSubscription;
use App\Models\UserCloudUsage;
use App\Enums\AccountStatus;

/**
 * User Model
 *
 * Represents a user in the application.
 * @property string $password
 */
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
        'subscription_id',
        'created_at',
        'updated_at',
        'account_status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
        'account_status',
        'password_reset_token',
        'subscription_id',
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
     * The attributes that should be appended to the model's array form.
     */
    protected $with = ['subscription', 'cloudUsage'];

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
    public function subscription(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(UserSubscription::class, 'user_id', 'uid')->where('status', 'active');
    }

    /**
     * Get the cloud usage for the user.
     */
    public function cloudUsage(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(UserCloudUsage::class, 'user_id', 'uid')->withDefault([
            'total_usage' => 0,
            'updated_at' => null,
        ]);
    }

    /**
     * Check if the user can upload a file based on their subscription quota.
     */
    public function canUpload($uploadSize): bool
    {
        $quota = $this->subscription?->type?->quota ?? 0;
        $used = $this->cloudUsage?->total ?? 0;
        return ($quota - $used) >= $uploadSize;
    }

    /**
     * Get the user's remaining storage space.
     */
    public function remainingStorage(): int
    {
        $quota = $this->subscription?->type?->quota ?? 0;
        $used = $this->cloudUsage?->total_usage ?? 0;
        return $quota - $used;
    }

    /**
     * Get the user's unique identifier.
     */
    public function updateCloudUsage(int $size): void
    {
        $usage = $this->cloudUsage()->first();
        $usage->increment('total_usage', $size);
    }

    /**
     * Account Verified
     */
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
