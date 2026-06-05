<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productUid = (string) $this->route('productUid');

        return [
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($productUid, 'uid'),
            ],
            'slug' => [
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($productUid, 'uid'),
            ],
            'name' => ['required', 'string', 'max:160'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'long_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'purchase_type' => ['sometimes', 'string', Rule::in(['one_time', 'monthly', 'annually'])],
            'price_amount' => ['required', 'integer', 'min:0'],
            'promotion_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'currency' => ['required', 'string', 'size:3'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
