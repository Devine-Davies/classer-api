<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlanCreateRequest extends FormRequest
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
            'quota' => 'nullable|integer|min:0',
            'type' => 'nullable|string|max:120',
            'duration' => 'nullable|string|max:255',
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
        ];
    }
}
