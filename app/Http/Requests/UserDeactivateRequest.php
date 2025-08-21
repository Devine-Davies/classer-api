<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserDeactivateRequest extends FormRequest
{
    /**
     * Only the authenticated user may deactivate their own account.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->id() === $this->user()->id;
    }

    /**
     * No input fields to validate for deactivation.
     */
    public function rules(): array
    {
        return [];
    }
}
