<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow the authenticated user to update their own profile
        return auth()->check() && auth()->id() === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'dob'  => 'sometimes|nullable|date',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
