<?php

namespace App\Http\Resources;

use App\Enums\AccountStatus;
use Illuminate\Http\Request;
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
            'created_at' => $this->created_at,

            'account_status' => $this->account_status?->value,
            'account_status_label' => $this->formatAccountStatus($this->account_status),

            'updated_at' => $this->updated_at,

            'subscription' => $this->subscription
                ? new SubscriptionResource($this->subscription)
                : null,

            'cloud_usage' => $this->cloudUsage
                ? new CloudUsageResource($this->cloudUsage)
                : null,
        ];
    }

    private function formatAccountStatus(?AccountStatus $status): string
    {
        return match ($status) {
            AccountStatus::INACTIVE => 'Inactive',
            AccountStatus::VERIFIED => 'Verified',
            AccountStatus::SUSPENDED => 'Suspended',
            AccountStatus::DEACTIVATED => 'Deactivated',
            default => 'Unknown',
        };
    }
}