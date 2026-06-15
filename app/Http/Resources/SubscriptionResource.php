<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'userId' => $this->user_id,
            'planId' => $this->plan_id,
            'status' => $this->status,
            'expirationDate' => $this->expiration_date,
            'autoRenew' => $this->auto_renew,
            'autoRenewDate' => $this->auto_renew_date,
            'cancellationDate' => $this->cancellation_date,
            'cancellationReason' => $this->cancellation_reason,
            'transactionId' => $this->transaction_id,
            'updatedBy' => $this->updated_by,
            'notes' => $this->notes,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'plan' => new SubscriptionTypeResource($this->whenLoaded('plan')),
        ];
    }
}
