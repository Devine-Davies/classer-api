<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category,
            'sortOrder' => $this->sort_order,
            'isPublished' => (bool) $this->is_published,

            'createdAt' => $this->created_at?->format('Y-m-d H:i:s'),
            'createdAtFormatted' => $this->created_at?->format('d M Y'),
            'updatedAt' => $this->updated_at?->format('Y-m-d H:i:s'),
            'updatedAtFormatted' => $this->updated_at?->format('d M Y'),
        ];
    }
}
