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
            'title' => $this->title,
            'code' => $this->code,
            'short_description' => $this->short_description,
            'catalog_item' => $this->catalogItem
                ? new CatalogItemResource($this->catalogItem)
                : null,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
