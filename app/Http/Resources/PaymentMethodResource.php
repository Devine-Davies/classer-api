<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'user_id' => $this->user_id,
            'is_default' => $this->is_default,
            'provider' => $this->provider,
            'type' => $this->type,
            'brand' => $this->brand,
            'last4' => $this->last4,
            'exp_month' => $this->exp_month,
            'exp_year' => $this->exp_year,
            'stripe_customer_id' => $this->stripe_customer_id,
            'stripe_transaction_id' => $this->stripe_transaction_id,
            'stripe_payment_method_id' => $this->stripe_payment_method_id,
            'revoked_at' => $this->revoked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
