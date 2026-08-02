<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $createdAt = $this->dateValue('created_at');
        $updatedAt = $this->dateValue('updated_at');

        return [
            'uid' => data_get($this->resource, 'uid'),
            'question' => data_get($this->resource, 'question'),
            'answer' => data_get($this->resource, 'answer'),
            'category' => data_get($this->resource, 'category'),
            'sortOrder' => (int) data_get($this->resource, 'sort_order', 0),
            'isPublished' => (bool) data_get($this->resource, 'is_published', false),

            'createdAt' => $createdAt?->format('Y-m-d H:i:s'),
            'createdAtFormatted' => $createdAt?->format('d M Y'),
            'updatedAt' => $updatedAt?->format('Y-m-d H:i:s'),
            'updatedAtFormatted' => $updatedAt?->format('d M Y'),
        ];
    }

    protected function dateValue(string $key): ?Carbon
    {
        $value = data_get($this->resource, $key);

        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }
}
