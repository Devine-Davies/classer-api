<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CloudUsageResource;
use App\Http\Resources\SubscriptionResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'name' => $this->name,
            'email' => $this->email,
            'dob' => $this->dob,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
            'cloud_usage' => new CloudUsageResource($this->whenLoaded('cloudUsage')),
        ];
    }
}
