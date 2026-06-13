<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'sellable_type' => $this->sellable_type,
            'sellable_id' => $this->sellable_id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'title' => $this->title,
            'price_amount' => $this->price_amount,
            'promotion_percentage' => $this->promotion_percentage,
            'currency' => $this->currency,
            'is_active' => $this->is_active,
            'image_url' => $this->image_url,
            'promotion_eligible' => $this->promotion_eligible,
            'discount_code_eligible' => $this->discount_code_eligible,
            'shipping_required' => $this->shipping_required,
            'sellable' => $this->whenLoaded('sellable', function () {
                if (! $this->sellable) {
                    return null;
                }

                return [
                    'uid' => $this->sellable->uid,
                    'title' => $this->sellable->title ?? $this->sellable->name ?? null,
                    'code' => $this->sellable->code ?? null,
                    'slug' => $this->sellable->slug ?? null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
