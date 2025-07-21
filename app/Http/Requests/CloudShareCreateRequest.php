<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates payload when requesting S3 presigned URLs
 * and creating a new CloudShare.
 */
class CloudShareCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'resourceId'            => ['required', 'string'],
            'entities'              => ['required', 'array', 'min:1'],
            'entities.*.uid'        => ['required', 'string'],
            'entities.*.sourceFile' => ['required', 'string'],
            'entities.*.contentType' => ['required', 'string'],
            'entities.*.size'       => ['required', 'integer', 'min:1'],
        ];
    }
}
