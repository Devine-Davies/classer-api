<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DiscountCodeCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:120',

            'discountPercentage' => 'required|integer|min:0|max:100',
            'maxDiscountPercentage' => 'nullable|integer|min:0|max:100',
            'minOrderAmount' => 'nullable|integer|min:0',

            'catalogItemId' => 'nullable|integer|exists:catalog_items,id',
            'assignedUserId' => 'nullable|integer|exists:users,id',
            'assignedEmail' => 'nullable|email|max:255',

            'isActive' => 'nullable|boolean',
            'usageLimit' => 'nullable|integer|min:0',
            'usageCount' => 'nullable|integer|min:0',
            'oneUsePerCustomer' => 'nullable|boolean',

            'startsAt' => 'nullable|date',
            'expiresAt' => 'nullable|date|after_or_equal:startsAt',
            'disabledAt' => 'nullable|date',

            'internalNote' => 'nullable|string|max:2000',

            'createdByUserId' => 'nullable|integer|exists:users,id',
            'updatedByUserId' => 'nullable|integer|exists:users,id',
            'disabledByUserId' => 'nullable|integer|exists:users,id',
        ];
    }

    public function discountCodePayload(): array
    {
        $data = $this->validated();

        return [
            'code' => $data['code'],

            'discount_percentage' => $data['discountPercentage'],
            'max_discount_percentage' => $data['maxDiscountPercentage'] ?? null,
            'min_order_amount' => $data['minOrderAmount'] ?? null,

            'catalog_item_id' => $data['catalogItemId'] ?? null,
            'assigned_user_id' => $data['assignedUserId'] ?? null,
            'assigned_email' => $data['assignedEmail'] ?? null,

            'is_active' => $this->boolean('isActive'),
            'usage_limit' => $data['usageLimit'] ?? null,
            'usage_count' => $data['usageCount'] ?? 0,
            'one_use_per_customer' => $this->boolean('oneUsePerCustomer'),

            'starts_at' => $data['startsAt'] ?? null,
            'expires_at' => $data['expiresAt'] ?? null,
            'disabled_at' => $data['disabledAt'] ?? null,

            'internal_note' => $data['internalNote'] ?? null,

            'created_by_user_id' => $data['createdByUserId'] ?? null,
            'updated_by_user_id' => $data['updatedByUserId'] ?? null,
            'disabled_by_user_id' => $data['disabledByUserId'] ?? null,
        ];
    }
}
