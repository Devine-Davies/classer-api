<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Enums\AccountStatus;
use App\Enums\RegistrationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * Represents a user in the application.
 *
 * @property string $password
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uid',
        'name',
        'email',
        'password',
        'email_verification_token',
        'password_reset_token',
        'created_at',
        'updated_at',
        'account_status',
        'registration_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int,
     * string>
     */
    protected $hidden = ['password', 'remember_token', 'email_verification_token', 'password_reset_token', 'registration_type'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registration_type' => RegistrationType::class,
        'account_status' => AccountStatus::class,
        'password' => 'hashed',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     */
    protected $with = ['subscription', 'cloudUsage'];

    /**
     * Create a new User instance.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Boot the model and set up event listeners for creating, created, and updated events.
     */
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->uid)) {
                $model->uid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the subscription for the user.
     */
    public function subscription(): hasOne
    {
        return $this->hasOne(UserSubscription::class, 'user_id', 'uid')->where('status', 'active');
    }

    /**
     * Check if the user has an active subscription.
     *
     * @return bool|null Returns true if active, false if inactive, null if no subscription
     */
    public function activeSubscription(): ?bool
    {
        $subscription = $this->subscription()->first();

        // No subscription
        if (! $subscription) {
            return null;
        }

        // Expired subscription
        if ($subscription->expiration_date && $subscription->expiration_date->isPast()) {
            return null;
        }

        // Otherwise, return whether it's active
        return $subscription->status === 'active';
    }

    /**
     * Get the cloud usage for the user.
     */
    public function cloudUsage(): hasOne
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
        $quota = $this->subscription?->plan?->quota ?? 0;
        $used = $this->cloudUsage?->total ?? 0;

        return $quota - $used >= $uploadSize;
    }

    /**
     * Get the user's remaining storage space.
     */
    public function remainingStorage(): int
    {
        $quota = $this->subscription?->plan?->quota ?? 0;
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
     */
    public function accountInactive(): bool
    {
        return $this->account_status === AccountStatus::INACTIVE;
    }

    /**
     * Account Deactivated
     */
    public function accountDeactivated(): bool
    {
        return $this->account_status === AccountStatus::DEACTIVATED;
    }

    /**
     * Account Suspended
     */
    public function accountSuspended(): bool
    {
        return $this->account_status === AccountStatus::SUSPENDED;
    }
}
