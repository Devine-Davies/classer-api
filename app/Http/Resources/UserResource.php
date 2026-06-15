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
            'accountStatus' => $this->account_status,
            'accountStatusLabel' => $this->formatAccountStatus($this->account_status),
            'updatedAt' => $this->updated_at,
            'subscription' => $this->subscription
                ? new SubscriptionResource($this->subscription)
                : null,

            'cloudUsage' => $this->cloudUsage
                ? new CloudUsageResource($this->cloudUsage)
                : null,
        ];
    }

    private function formatAccountStatus(?AccountStatus $status): string
    {
        return match ($status) {
            AccountStatus::INACTIVE => 'inactive',
            AccountStatus::VERIFIED => 'verified',
            AccountStatus::SUSPENDED => 'suspended',
            AccountStatus::DEACTIVATED => 'deactivated',
            default => 'unknown',
        };
    }
}
