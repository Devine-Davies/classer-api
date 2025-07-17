<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionEnableRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only authenticated users can enable their own subscription
        return auth()->check() && auth()->id() === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'subType' => 'required|exists:subscription_types,id',
        ];
    }

    protected function prepareForValidation(): void
    {
        // nothing for now, but handy if you need to normalize input later
    }
}
