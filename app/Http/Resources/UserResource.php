<?php

namespace App\Http\Resources;

use App\Enums\AccountStatus;
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

            'subscription' => $this->whenLoaded('subscription', function () {
                return $this->subscription
                    ? new SubscriptionResource($this->subscription)
                    : null;
            }),

            'cloudUsage' => $this->whenLoaded('cloudUsage', function () {
                return $this->cloudUsage
                    ? new CloudUsageResource($this->cloudUsage)
                    : null;
            }),
        ];
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
