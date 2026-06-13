<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'code' => $this->code,
            'title' => $this->title,
            'type' => $this->type,
            'duration' => $this->duration,
            'quota' => $this->quota,
            'catalogItem' => $this->catalogItem
                ? new CatalogItemResource($this->catalogItem)
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
