<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FaqUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating a FAQ.
     */
    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'string', 'max:500'],
            'answer' => ['sometimes', 'string', 'max:5000'],
            'category' => ['nullable', 'string', 'max:120'],
            'sortOrder' => ['nullable', 'integer', 'min:0'],
            'isPublished' => ['sometimes', 'boolean'],
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
            'question' => 'question',
            'answer' => 'answer',
            'category' => 'category',
            'sortOrder' => 'sort_order',
        ];

        foreach ($map as $inputKey => $payloadKey) {
            if (array_key_exists($inputKey, $data)) {
                $payload[$payloadKey] = $data[$inputKey];
            }
        }

        if (array_key_exists('isPublished', $data)) {
            $payload['is_published'] = $this->boolean('isPublished');
        }

        return $payload;
    }
}
