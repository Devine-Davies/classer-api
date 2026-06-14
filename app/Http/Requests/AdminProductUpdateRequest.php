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
            'slug' => [
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($productUid, 'uid'),
            ],
            'name' => ['required', 'string', 'max:160'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }
}
