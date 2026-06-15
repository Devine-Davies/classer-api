<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'shortDescription' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get the payload for creating or updating a product.
     *
     * @return array<string, mixed>
     */
    public function productPayload(): array
    {
        $data = $this->validated();

        return [
            'title' => $data['title'],
            'short_description' => $data['shortDescription'] ?? null,
            'description' => $data['description'] ?? null,
        ];
    }
}
