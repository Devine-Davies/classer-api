<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CloudUsageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'shareUsage' => $this->share_usage,
            'backupUsage' => $this->backup_usage,
            'totalUsage' => $this->total_usage,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
