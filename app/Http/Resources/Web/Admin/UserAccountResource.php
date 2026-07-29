<?php

namespace App\Http\Resources\Web\Admin;

use App\Enums\AccountStatus;
use App\Http\Resources\CloudUsageResource;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        $subscriptions = collect($this->subscriptions ?? []);
        $activeSubscriptions = $subscriptions->where('status', 'active');

        $status = $this->formatAccountStatus($this->account_status);

        return [
            'uid' => $this->uid,
            'name' => $this->name,
            'email' => $this->email,
            'dob' => $this->dob,

            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,

            'accountStatus' => $this->account_status,
            'accountStatusLabel' => $status['label'],
            'accountStatusTone' => $status['tone'],
            'accountStatusClass' => $this->formatAccountStatusCssClass($status['label']),
            'planLabel' => $this->resolvePlanLabel(),
            'activePlanLabels' => $activeSubscriptions->pluck('plan.title')->filter()->values()->all(),
            'activeSubscriptionCount' => $activeSubscriptions->count(),
            'subscriptionCount' => $subscriptions->count(),
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

        if ($status instanceof AccountStatus) {
            return match ($status) {
                AccountStatus::INACTIVE => ['label' => 'Inactive', 'tone' => 'warning'],
                AccountStatus::VERIFIED => ['label' => 'Verified', 'tone' => 'success'],
                AccountStatus::SUSPENDED => ['label' => 'Suspended', 'tone' => 'danger'],
                AccountStatus::DEACTIVATED => ['label' => 'Deactivated', 'tone' => 'danger'],
            };
        }

        return match ((int) $status) {
            AccountStatus::INACTIVE->value => ['label' => 'Inactive', 'tone' => 'warning'],
            AccountStatus::VERIFIED->value => ['label' => 'Verified', 'tone' => 'success'],
            AccountStatus::SUSPENDED->value => ['label' => 'Suspended', 'tone' => 'danger'],
            AccountStatus::DEACTIVATED->value => ['label' => 'Deactivated', 'tone' => 'danger'],
            default => ['label' => 'Unknown', 'tone' => 'secondary'],
        };
    }

    protected function formatAccountStatusCssClass(string $label): string
    {
        return match (strtolower($label)) {
            'active', 'verified', 'enabled' => 'emerald',
            'blocked', 'banned', 'disabled', 'deactivated' => 'rose',
            'inactive' => 'slate',
            'suspended' => 'amber',
            'pending' => 'amber',
            default => 'slate',
        };
    }

    protected function resolvePlanLabel(): string
    {
        return (string) data_get($this->resource, 'plan.title')
            ?: (string) data_get($this->resource, 'plan.code')
            ?: (string) data_get($this->resource, 'plan_id')
            ?: '—';
    }
}
