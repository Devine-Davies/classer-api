<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a CloudEntity model into a structured JSON response.
 */
class CloudEntityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'uid' => $this->uid,
            'type' => $this->mime_type,
            'size' => $this->expected_size,
            'role' => $this->object_role?->value,
            'originalName' => $this->original_name,
            'expectedSize' => $this->expected_size,
            'actualSize' => $this->actual_size,
            'status' => $this->status?->value,
            'uploadUrl' => $this->upload_url,
            'eTag' => $this->e_tag,
        ];
    }
}
