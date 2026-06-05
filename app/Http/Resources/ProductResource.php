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
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'purchase_type' => $this->purchase_type,
            'price_amount' => $this->price_amount,
            'currency' => $this->currency,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
