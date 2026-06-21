<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountCodeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are applied.
     * It allows you to modify the request data before validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('code')) {
            $merge['code'] = strtoupper(trim((string) $this->input('code')));
        }

        if ($this->has('assignedEmail')) {
            $assignedEmail = $this->input('assignedEmail');

            $merge['assignedEmail'] = $assignedEmail
                ? strtolower(trim((string) $assignedEmail))
                : null;
        }

        $this->merge($merge);
    }

    /**
     * Validation rules for updating a discount code.
     */
    public function rules(): array
    {
        $discountCodeUid = $this->route('discountCodeUid');

        return [
            'code' => [
                'sometimes',
                'string',
                'max:64',
                'alpha_dash',
                Rule::unique('discount_codes', 'code')->ignore($discountCodeUid, 'uid'),
            ],

            'discountPercentage' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'maxDiscountPercentage' => ['nullable', 'integer', 'min:1', 'max:99'],
            'minOrderAmount' => ['nullable', 'integer', 'min:1'],

            // Use this if your form sends catalog item database ID:
            // 'catalogItemId' => ['nullable', 'integer', 'exists:catalog_items,id'],

            // Use this instead if your form sends catalog item UID:
            'catalogItemId' => ['nullable', 'uuid', 'exists:catalog_items,uid'],

            'assignedUserId' => ['nullable', 'uuid', 'exists:users,uid'],
            'assignedEmail' => ['nullable', 'email', 'max:120'],

            'isActive' => ['sometimes', 'boolean'],
            'usageLimit' => ['nullable', 'integer', 'min:1'],
            'oneUsePerCustomer' => ['sometimes', 'boolean'],

            'startsAt' => ['nullable', 'date'],
            'expiresAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'internalNote' => ['nullable', 'string', 'max:1000'],

            'disabledAt' => ['nullable', 'date'],
            'disabledByUserId' => ['nullable', 'uuid', 'exists:users,uid'],
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'expiresAt.after_or_equal' => 'The expiry date must be the same as or later than the start date.',
            'expiresAt.after' => 'The expiry date must be after the start date.',
        ];
    }

    /**
     * Get the validated data and prepare it for the update payload.
     */
    public function payload(): array
    {
        $data = $this->validated();

        $payload = [];

        $map = [
            'code' => 'code',

            'discountPercentage' => 'discount_percentage',
            'maxDiscountPercentage' => 'max_discount_percentage',
            'minOrderAmount' => 'min_order_amount',

            'catalogItemId' => 'catalog_item_id',
            'assignedUserId' => 'assigned_user_id',
            'assignedEmail' => 'assigned_email',

            'usageLimit' => 'usage_limit',

            'startsAt' => 'starts_at',
            'expiresAt' => 'expires_at',
            'internalNote' => 'internal_note',

            'disabledAt' => 'disabled_at',
            'disabledByUserId' => 'disabled_by_user_id',
        ];

        foreach ($map as $inputKey => $payloadKey) {
            if (array_key_exists($inputKey, $data)) {
                $payload[$payloadKey] = $data[$inputKey];
            }
        }

        if (array_key_exists('isActive', $data)) {
            $payload['is_active'] = $this->boolean('isActive');

            if ($payload['is_active'] === false) {
                $payload['disabled_at'] = $payload['disabled_at'] ?? now();
                $payload['disabled_by_user_id'] = optional($this->user())->uid;
            }

            if ($payload['is_active'] === true) {
                $payload['disabled_at'] = null;
                $payload['disabled_by_user_id'] = null;
            }
        }

        if (array_key_exists('oneUsePerCustomer', $data)) {
            $payload['one_use_per_customer'] = $this->boolean('oneUsePerCustomer');
        }

        $payload['updated_by_user_id'] = optional($this->user())->uid;

        return $payload;
    }
}
