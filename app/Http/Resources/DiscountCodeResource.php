<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountCodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'code' => $this->code,
            'discountPercentage' => $this->discount_percentage,
            'maxDiscountPercentage' => $this->max_discount_percentage,
            'minOrderAmount' => $this->min_order_amount,
            'catalogItemId' => $this->catalog_item_id,
            'catalogItem' => $this->whenLoaded('catalogItem', function () {
                return $this->catalogItem ? new CatalogItemResource($this->catalogItem) : null;
            }),
            'assignedUserId' => $this->assigned_user_id,
            'assignedEmail' => $this->assigned_email,
            'isActive' => $this->is_active,
            'usageLimit' => $this->usage_limit,
            'usageCount' => $this->usage_count,
            'oneUsePerCustomer' => $this->one_use_per_customer,
            'startsAt' => $this->starts_at,
            'expiresAt' => $this->expires_at,
            'disabledAt' => $this->disabled_at,
            'internalNote' => $this->internal_note,
            'createdByUserId' => $this->created_by_user_id,
            'updatedByUserId' => $this->updated_by_user_id,
            'disabledByUserId' => $this->disabled_by_user_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
