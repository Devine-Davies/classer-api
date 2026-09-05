<?php

namespace App\Http\Resources;

use App\Enums\AccountStatus;
use App\Enums\CloudStorageKind;
use App\Models\PlanEntitlement;
use App\Models\UserSubscription;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uid' => $this->uid,
            'name' => $this->name,
            'email' => $this->email,
            'dob' => $this->dob,

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            'accountStatus' => $this->account_status?->value,
            'accountStatusLabel' => $this->formatAccountStatus($this->account_status)['label'],
            'accountStatusTone' => $this->formatAccountStatus($this->account_status)['tone'],

            'subscriptions' => $this->whenLoaded('subscriptions', fn () => $this->subscriptions
                ->map(fn (UserSubscription $subscription): array => $this->subscription($subscription))
                ->values()),
        ];
    }

    private function subscription(UserSubscription $subscription): array
    {
        return [
            'uid' => $subscription->uid,
            'status' => $subscription->status,
            'expiresAt' => $subscription->expiration_date?->toIso8601String(),
            'plan' => [
                'uid' => $subscription->plan?->uid,
                'code' => $subscription->plan?->code,
                'title' => $subscription->plan?->title,
            ],
            'capabilities' => $subscription->plan?->entitlements
                ->filter(fn (PlanEntitlement $entitlement): bool => $this->storageKind($entitlement) !== null)
                ->map(fn (PlanEntitlement $entitlement): array => $this->capability($entitlement))
                ->values()
                ->all() ?? [],
        ];
    }

    private function capability(PlanEntitlement $entitlement): array
    {
        $kind = $this->storageKind($entitlement);
        $quota = (int) $entitlement->quota;
        $used = $kind === null ? 0 : (int) ($this->cloudUsage?->{$kind->usageColumn()} ?? 0);

        return [
            'type' => $kind?->capability() === 'cloud_share' ? 'cloudShare' : 'cloudBackup',
            'quotaBytes' => $quota,
            'usedBytes' => $used,
            'remainingBytes' => max(0, $quota - $used),
        ];
    }

    private function storageKind(PlanEntitlement $entitlement): ?CloudStorageKind
    {
        return match ($entitlement->capability) {
            CloudStorageKind::SHARE->capability() => CloudStorageKind::SHARE,
            CloudStorageKind::BACKUP->capability() => CloudStorageKind::BACKUP,
            default => null,
        };
    }

    public function formatAccountStatus($status = null): array
    {
        $status = $status ?? $this->account_status;

        return match ($status) {
            AccountStatus::INACTIVE => ['label' => 'Inactive', 'tone' => 'warning'],
            AccountStatus::VERIFIED => ['label' => 'Verified', 'tone' => 'success'],
            AccountStatus::SUSPENDED => ['label' => 'Suspended', 'tone' => 'danger'],
            AccountStatus::DEACTIVATED => ['label' => 'Deactivated', 'tone' => 'danger'],
            default => ['label' => 'Unknown', 'tone' => 'secondary'],
        };
    }
}
