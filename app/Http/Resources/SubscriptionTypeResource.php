<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'title' => $this->title,
            'code' => $this->code,
            'duration' => $this->duration,
            'entitlements' => $this->whenLoaded('entitlements', function () {
                return $this->entitlements->map(fn ($entitlement): array => [
                    'capability' => $entitlement->capability,
                    'quota' => $entitlement->quota,
                ])->values();
            }),
            'catalogItem' => $this->whenLoaded('catalogItem', function () {
                return [
                    'uid' => $this->catalogItem->uid,
                    'sku' => $this->catalogItem->sku,
                    'slug' => $this->catalogItem->slug,
                    'title' => $this->catalogItem->title,
                    'priceAmount' => $this->catalogItem->price_amount,
                    'currency' => $this->catalogItem->currency,
                    'isActive' => $this->catalogItem->is_active,
                    'imageUrl' => $this->catalogItem->image_url,
                    'promotionEligible' => $this->catalogItem->promotion_eligible,
                    'discountCodeEligible' => $this->catalogItem->discount_code_eligible,
                    'shippingRequired' => $this->catalogItem->shipping_required,
                ];
            }),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
