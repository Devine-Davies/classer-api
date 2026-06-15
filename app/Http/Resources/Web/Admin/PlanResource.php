<?php

namespace App\Http\Resources\Web\Admin;

use App\Http\Resources\CatalogItemResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'uid' => $this->uid,
            'code' => $this->code,
            'title' => $this->title,
            'type' => $this->type,
            'duration' => $this->duration,
            'niceDuration' => $this->formatDuration($this->duration),
            'quota' => $this->quota,
            'niceQuota' => $this->formatQuota($this->quota),
            'shortDescription' => $this->short_description,
            'description' => $this->description,
            'catalogItem' => $this->loadMissing('catalogItem')->catalogItem ? new CatalogItemResource($this->catalogItem) : null,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }

    /*
    * Format the duration in a human-readable format.
    *
    * @param int|null $duration
    * @return string
    */
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

    /*
    * Format the quota in a human-readable format.
    * @param int|null $quota
    * @return string
    */
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
