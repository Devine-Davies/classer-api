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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
