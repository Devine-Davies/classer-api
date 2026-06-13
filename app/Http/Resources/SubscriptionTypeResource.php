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
            'quota' => $this->quota,
            'type' => $this->type,
            'duration' => $this->duration,
            'catalog_item' => $this->whenLoaded('catalogItem', function () {
                return [
                    'uid' => $this->catalogItem->uid,
                    'sku' => $this->catalogItem->sku,
                    'slug' => $this->catalogItem->slug,
                    'title' => $this->catalogItem->title,
                    'price_amount' => $this->catalogItem->price_amount,
                    'currency' => $this->catalogItem->currency,
                    'is_active' => $this->catalogItem->is_active,
                    'image_url' => $this->catalogItem->image_url,
                    'promotion_eligible' => $this->catalogItem->promotion_eligible,
                    'discount_code_eligible' => $this->catalogItem->discount_code_eligible,
                    'shipping_required' => $this->catalogItem->shipping_required,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
