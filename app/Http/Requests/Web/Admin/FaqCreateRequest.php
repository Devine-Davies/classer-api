<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class FaqCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'category' => 'nullable|string|max:120',
            'sortOrder' => 'nullable|integer|min:0',
            'isPublished' => 'nullable|boolean',
        ];
    }

    public function faqPayload(): array
    {
        $data = $this->validated();

        return [
            'question' => $data['question'],
            'answer' => $data['answer'],
            'category' => $data['category'] ?? null,
            'sort_order' => $data['sortOrder'] ?? 0,
            'is_published' => $this->boolean('isPublished'),
        ];
    }
}
