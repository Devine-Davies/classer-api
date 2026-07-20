<?php

namespace App\Http\Requests\Web\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PostCreateRequest extends FormRequest
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
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'type' => 'required|string|in:blog,story',
            'date' => 'required|date_format:Y-m-d',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'thumbnail' => 'required|string|max:255',
            'alt' => 'nullable|string|max:255',
            'markdown' => 'required|string',
        ];
    }

    /**
     * Get the payload for creating or updating a post.
     *
     * @return array{metadata: array<string, mixed>, markdown: string}
     */
    public function payload(): array
    {
        $data = $this->validated();

        return [
            'metadata' => [
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => $data['type'],
                'date' => $data['date'],
                'author' => $data['author'],
                'description' => $data['description'] ?? '',
                'thumbnail' => $data['thumbnail'],
                'alt' => $data['alt'] ?? $data['title'],
            ],
            'markdown' => $data['markdown'],
        ];
    }
}
