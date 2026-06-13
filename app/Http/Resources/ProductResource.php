<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'sku' => $this->sku,
            'slug' => $this->slug,
            'name' => $this->name,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'catalog_item' => $this->whenLoaded('catalogItem', function () {
                return [
                    'uid' => $this->catalogItem->uid,
                    'sku' => $this->catalogItem->sku,
                    'slug' => $this->catalogItem->slug,
                    'title' => $this->catalogItem->title,
                    'price_amount' => $this->catalogItem->price_amount,
                    'promotion_percentage' => $this->catalogItem->promotion_percentage,
                    'currency' => $this->catalogItem->currency,
                    'is_active' => $this->catalogItem->is_active,
                    'image_url' => $this->catalogItem->image_url,
                    'promotion_eligible' => $this->catalogItem->promotion_eligible,
                    'discount_code_eligible' => $this->catalogItem->discount_code_eligible,
                    'shipping_required' => $this->catalogItem->shipping_required,
                ];
            }),
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
