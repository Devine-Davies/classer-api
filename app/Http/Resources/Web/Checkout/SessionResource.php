<?php

namespace App\Http\Resources\Web\Checkout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'clientSecret' => $this->resource['client_secret'],
            'publishableKey' => (string) config('services.stripe.key'),
            'order' => new OrderResource($this->resource['order']),
            'payment' => [
                'uid' => $this->resource['payment']->uid,
                'status' => $this->resource['payment']->status,
                'stripePaymentIntentId' => $this->resource['payment']->stripe_payment_intent_id,
            ],
        ];
    }
}
