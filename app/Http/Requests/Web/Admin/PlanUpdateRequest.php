<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'quota' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:255',
            'shortDescription' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',

            'catalogItem.title' => 'required|string|max:255',
            'catalogItem.shortDescription' => 'nullable|string|max:500',
            'catalogItem.description' => 'nullable|string|max:2000',
            'catalogItem.priceAmount' => 'required|integer|min:0',
            'catalogItem.currency' => 'required|string|size:3',
            'catalogItem.promotionPercentage' => 'nullable|integer|min:0|max:100',
            'catalogItem.isPublished' => 'nullable|boolean',
            'catalogItem.imageUrl' => 'nullable|string|max:2048',
            'catalogItem.promotionEligible' => 'nullable|boolean',
            'catalogItem.discountCodeEligible' => 'nullable|boolean',
            'catalogItem.shippingRequired' => 'nullable|boolean',
            'catalogItem.slug' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the payload for creating or updating a plan.
     *
     * @return array<string, mixed>
     */
    public function planPayload(): array
    {
        $data = $this->validated();

        return [
            'title' => $data['title'],
            'quota' => $data['quota'] ?? null,
            'type' => $data['type'] ?? null,
            'duration' => $data['duration'] ?? null,
            'short_description' => $data['shortDescription'] ?? null,
            'description' => $data['description'] ?? null,

            'catalog_item' => [
                'title' => $data['catalogItem']['title'],
                'short_description' => $data['catalogItem']['shortDescription'] ?? null,
                'description' => $data['catalogItem']['description'] ?? null,
                'price_amount' => $data['catalogItem']['priceAmount'],
                'currency' => strtoupper($data['catalogItem']['currency']),
                'promotion_percentage' => $data['catalogItem']['promotionPercentage'] ?? null,
                'is_published' => $this->boolean('catalogItem.isPublished'),
                'image_url' => $data['catalogItem']['imageUrl'] ?? null,
                'promotion_eligible' => $this->boolean('catalogItem.promotionEligible'),
                'discount_code_eligible' => $this->boolean('catalogItem.discountCodeEligible'),
                'shipping_required' => $this->boolean('catalogItem.shippingRequired'),
                'slug' => $data['catalogItem']['slug'] ?? null,
            ],
        ];
    }
}
