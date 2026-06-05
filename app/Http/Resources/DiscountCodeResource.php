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
            'discount_percentage' => $this->discount_percentage,
            'max_discount_percentage' => $this->max_discount_percentage,
            'min_order_amount' => $this->min_order_amount,
            'product_id' => $this->product_id,
            'assigned_user_id' => $this->assigned_user_id,
            'assigned_email' => $this->assigned_email,
            'is_active' => $this->is_active,
            'usage_limit' => $this->usage_limit,
            'usage_count' => $this->usage_count,
            'one_use_per_customer' => $this->one_use_per_customer,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'disabled_at' => $this->disabled_at,
            'internal_note' => $this->internal_note,
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'disabled_by_user_id' => $this->disabled_by_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
