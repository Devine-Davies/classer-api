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
            'catalog_item_uid' => ['required_without_all:catalog_item_uids,product_uid,product_uids', 'uuid', 'exists:catalog_items,uid'],
            'catalog_item_uids' => ['required_without_all:catalog_item_uid,product_uid,product_uids', 'array', 'min:1'],
            'catalog_item_uids.*' => ['uuid', 'exists:catalog_items,uid'],
            'product_uid' => ['required_without_all:catalog_item_uid,catalog_item_uids,product_uids', 'uuid', 'exists:products,uid'],
            'product_uids' => ['required_without_all:catalog_item_uid,catalog_item_uids,product_uid', 'array', 'min:1'],
            'product_uids.*' => ['uuid', 'exists:products,uid'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:5'],
            'discount_code' => ['nullable', 'string', 'max:64'],
        ];
    }
}
