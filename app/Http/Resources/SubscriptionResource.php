<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\SubscriptionTypeResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'user_id' => $this->user_id,
            'subscription_id' => $this->subscription_id,
            'payment_method_id' => $this->payment_method_id,
            'status' => $this->status,
            'expiration_date' => $this->expiration_date,
            'auto_renew' => $this->auto_renew,
            'auto_renew_date' => $this->auto_renew_date,
            'cancellation_date' => $this->cancellation_date,
            'cancellation_reason' => $this->cancellation_reason,
            'transaction_id' => $this->transaction_id,
            'updated_by' => $this->updated_by,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'type' => new SubscriptionTypeResource($this->whenLoaded('type')),
        ];
    }
}
