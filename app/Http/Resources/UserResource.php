<?php

namespace App\Http\Resources;

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
            'accountStatusLabel' => $this->account_status?->label() ?? 'Unknown',
            'accountStatusTone' => $this->account_status?->badgeTone() ?? 'muted',

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
}
