<?php

namespace App\Http\Requests\Web\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $allowedCountryCodes = $this->loadAllowedCountryCodes();

        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
            'shipping_line_1' => ['required', 'string', 'max:255'],
            'shipping_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_state' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['required', 'string', 'max:32'],
            'shipping_country' => ['required', 'string', 'size:2', Rule::in($allowedCountryCodes)],
            'discount_code' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function loadAllowedCountryCodes(): array
    {
        $path = storage_path('app/public/shipping.json');

        if (! is_file($path) || ! is_readable($path)) {
            return ['GB'];
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            return ['GB'];
        }

        $codes = collect($decoded)
            ->map(function (mixed $item): ?string {
                if (! is_array($item)) {
                    return null;
                }

                $isPublished = array_key_exists('is_published', $item)
                    ? (bool) $item['is_published']
                    : true;

                if (! $isPublished) {
                    return null;
                }

                $code = strtoupper(trim((string) ($item['rmCountryCode'] ?? '')));

                if ($code === '' || strlen($code) !== 2) {
                    return null;
                }

                return $code;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($codes)) {
            return ['GB'];
        }

        return $codes;
    }
}
