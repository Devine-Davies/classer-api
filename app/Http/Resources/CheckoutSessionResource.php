<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'client_secret' => $this->resource['client_secret'],
            'publishable_key' => (string) config('services.stripe.key'),
            'order' => new OrderResource($this->resource['order']),
            'payment' => [
                'uid' => $this->resource['payment']->uid,
                'status' => $this->resource['payment']->status,
                'stripe_payment_intent_id' => $this->resource['payment']->stripe_payment_intent_id,
            ],
        ];
    }
}
