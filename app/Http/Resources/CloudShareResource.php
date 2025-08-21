<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CloudEntityResource;

/**
 * Transforms a CloudShare model into a JSON response.
 */
class CloudShareResource extends JsonResource
{
    /**
     * Transform the resource into an array for JSON output.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'uid'          => $this->uid,
            'user_id'      => $this->user_id,
            'resource_id'  => $this->resource_id,
            'size'         => $this->size,
            'deleted_at'   => optional($this->deleted_at)->toIso8601String(),
            'created_at'   => optional($this->created_at)->toIso8601String(),
            'updated_at'   => optional($this->updated_at)->toIso8601String(),

            // Include cloud entities if loaded
            'entities' => CloudEntityResource::collection(
                $this->whenLoaded('cloudEntities')
            ),
        ];
    }
}