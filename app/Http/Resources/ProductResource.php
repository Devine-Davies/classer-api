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
            'purchase_type' => $this->purchase_type,
            'price_amount' => $this->price_amount,
            'promotion_percentage' => $this->promotion_percentage,
            'currency' => $this->currency,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
