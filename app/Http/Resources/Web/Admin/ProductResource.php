<?php

namespace App\Http\Resources\Web\Admin;

use App\Http\Resources\CatalogItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'title' => $this->title,
            'code' => $this->code,
            'shortDescription' => $this->short_description,
            'catalogItem' => $this->catalogItem
                ? new CatalogItemResource($this->catalogItem)
                : null,
            'deletedAt' => $this->deleted_at,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
