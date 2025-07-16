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
            'uid'         => $this->uid,
            'type'        => $this->type,
            'size'        => $this->size,
            'upload_url'  => $this->upload_url,
            'e_tag'       => $this->e_tag,
            'expires_at'  => optional($this->expires_at)->toIso8601String(),
        ];
    }
}