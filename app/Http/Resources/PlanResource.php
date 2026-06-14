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
            'nice_duration' => $this->formatDuration($this->duration),
            'quota' => $this->quota,
            'nice_quota' => $this->formatQuota($this->quota),
            'short_description' => $this->short_description,
            'description' => $this->description,
            'catalog_item' => $this->loadMissing('catalogItem')->catalogItem ? new CatalogItemResource($this->catalogItem) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function formatDuration(?int $duration): string
    {
        if ($duration === null) {
            return 'Unlimited';
        }

        if ($duration < 60) {
            return $duration.' seconds';
        }

        if ($duration < 3600) {
            return number_format($duration / 60, 2).' minutes';
        }

        if ($duration < 86400) {
            return number_format($duration / 3600, 2).' hours';
        }

        return number_format($duration / 86400, 2).' days';
    }

    private function formatQuota(?int $quota): string
    {
        if ($quota === null) {
            return 'Unlimited';
        }

        if ($quota < 1024) {
            return $quota.' bytes';
        }

        if ($quota < 1048576) {
            return number_format($quota / 1024, 2).' KB';
        }

        if ($quota < 1073741824) {
            return number_format($quota / 1048576, 2).' MB';
        }

        return number_format($quota / 1073741824, 2).' GB';
    }
}
