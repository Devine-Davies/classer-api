<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a CloudShare model into a JSON response.
 */
class CloudShareResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON output.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'uid' => $this->uid,
            'userId' => $this->user_id,
            'resourceId' => $this->resource_id,
            'size' => $this->expected_size,
            'expectedSize' => $this->expected_size,
            'actualSize' => $this->actual_size,
            'status' => $this->status?->value,
            'publicUrl' => route('cloud-share.show', ['uid' => $this->uid]),
            'uploadExpiresAt' => optional($this->upload_expires_at)->toIso8601String(),
            'expiresAt' => optional($this->expires_at)->toIso8601String(),
            'completedAt' => optional($this->completed_at)->toIso8601String(),
            'validatedAt' => optional($this->validated_at)->toIso8601String(),
            'deletedAt' => optional($this->deleted_at)->toIso8601String(),
            'createdAt' => optional($this->created_at)->toIso8601String(),
            'updatedAt' => optional($this->updated_at)->toIso8601String(),

            // Include cloud entities if loaded
            'entities' => CloudEntityResource::collection(
                $this->whenLoaded('cloudEntities')
            ),
        ];
    }
}
