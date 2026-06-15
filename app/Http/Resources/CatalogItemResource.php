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
            'sellableType' => $this->sellable_type,
            'sellableId' => $this->sellable_id,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'title' => $this->title,
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'priceAmount' => $this->price_amount,
            'priceAmountFormatted' => number_format($this->price_amount / 100, 2).' '.strtoupper($this->currency),
            'promotionPercentage' => $this->promotion_percentage,
            'currency' => $this->currency,
            'isPublished' => $this->is_published,
            'imageUrl' => $this->image_url,
            'promotionEligible' => $this->promotion_eligible,
            'discountCodeEligible' => $this->discount_code_eligible,
            'shippingRequired' => $this->shipping_required,
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
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
