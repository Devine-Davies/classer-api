<?php

namespace App\Http\Resources\Web\Admin;

use App\Enums\AccountStatus;
use App\Http\Resources\CloudUsageResource;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAccountResource extends JsonResource
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

            'accountStatus' => $this->account_status,
            'accountStatusLabel' => $this->formatAccountStatus($this->account_status)['label'],
            'accountStatusTone' => $this->formatAccountStatus($this->account_status)['tone'],
            'subscriptions' => $this->whenLoaded('subscription', function () {
                return $this->subscriptions
                    ? SubscriptionResource::collection($this->subscriptions)->resolve()
                    : null;
            }),

            'cloudUsage' => $this->whenLoaded('cloudUsage', function () {
                return $this->cloudUsage
                    ? CloudUsageResource::make($this->cloudUsage)->resolve()
                    : null;
            }),
        ];
    }

    public function formatAccountStatus($status = null): array
    {
        $status = $status ?? $this->accountStatus;

        return match ($status) {
            AccountStatus::INACTIVE => ['label' => 'Inactive', 'tone' => 'warning'],
            AccountStatus::VERIFIED => ['label' => 'Verified', 'tone' => 'success'],
            AccountStatus::SUSPENDED => ['label' => 'Suspended', 'tone' => 'danger'],
            AccountStatus::DEACTIVATED => ['label' => 'Deactivated', 'tone' => 'danger'],
            default => ['label' => 'Unknown', 'tone' => 'secondary'],
        };
    }
}
