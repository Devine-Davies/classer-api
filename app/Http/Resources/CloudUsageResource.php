<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CloudUsageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'total_usage' => $this->total_usage,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
