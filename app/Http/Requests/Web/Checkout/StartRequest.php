<?php

namespace App\Http\Requests\Web\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class StartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'catalog_item_uids' => ['nullable', 'array', 'min:1'],
            'catalog_item_uids.*' => ['uuid', 'exists:catalog_items,uid'],

            'catalog_item_sku' => ['nullable', 'string', 'exists:catalog_items,sku'],
            'catalog_item_skus' => ['nullable', 'array', 'min:1'],
            'catalog_item_skus.*' => ['string', 'exists:catalog_items,sku'],

            'quantities' => ['nullable', 'array'],
            'quantities.*' => ['integer', 'min:1', 'max:99'],
        ];
    }
}
