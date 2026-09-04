<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CloudBackupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uid' => $this->uid,
            'resourceId' => $this->resource_id,
            'expectedSize' => $this->expected_size,
            'actualSize' => $this->actual_size,
            'status' => $this->status?->value,
            'completedAt' => optional($this->completed_at)->toIso8601String(),
            'validatedAt' => optional($this->validated_at)->toIso8601String(),
            'lastRestoredAt' => optional($this->last_restored_at)->toIso8601String(),
            'createdAt' => optional($this->created_at)->toIso8601String(),
            'updatedAt' => optional($this->updated_at)->toIso8601String(),
            'entities' => CloudEntityResource::collection(
                $this->whenLoaded('cloudEntities')
            ),
        ];
    }
}
