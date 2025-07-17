<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UserVerifyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        // Map camel-case key into Laravel’s password_confirmation
        if ($this->has('passwordConfirmation')) {
            $this->merge([
                'password_confirmation' => $this->input('passwordConfirmation'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'token'                 => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => 'The form contains errors, please make sure passwords match and are at least 6 characters long.',
            'errors'  => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
