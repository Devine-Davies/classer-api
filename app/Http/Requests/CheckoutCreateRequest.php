<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_uid' => ['required_without:product_uids', 'uuid', 'exists:products,uid'],
            'product_uids' => ['required_without:product_uid', 'array', 'min:1'],
            'product_uids.*' => ['uuid', 'exists:products,uid'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:5'],
            'discount_code' => ['nullable', 'string', 'max:64'],
        ];
    }
}
