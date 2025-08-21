<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Handles validation for listing CloudShare resources.
 */
class CloudShareIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Only authenticated users may list their shares
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     * No input fields required for listing.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}