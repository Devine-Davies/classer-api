<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloudBackupCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'resourceId' => ['required', 'string'],
            'entities' => ['required', 'array', 'min:1'],
            'entities.*.uid' => ['required', 'string'],
            'entities.*.sourceFile' => ['required', 'string'],
            'entities.*.contentType' => ['required', 'string'],
            'entities.*.size' => ['required', 'integer', 'min:1'],
        ];
    }
}
