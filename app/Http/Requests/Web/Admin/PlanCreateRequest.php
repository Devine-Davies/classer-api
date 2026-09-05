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
            'duration' => 'nullable|string|max:255',
            'entitlements' => 'nullable|array',
            'entitlements.cloud_share.enabled' => 'nullable|boolean',
            'entitlements.cloud_share.quota' => 'nullable|required_if:entitlements.cloud_share.enabled,1|integer|min:1',
            'entitlements.cloud_backup.enabled' => 'nullable|boolean',
            'entitlements.cloud_backup.quota' => 'nullable|required_if:entitlements.cloud_backup.enabled,1|integer|min:1',
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
            'duration' => $data['duration'] ?? null,
            'entitlements' => $this->entitlementPayload($data),
        ];
    }

    private function entitlementPayload(array $data): array
    {
        return collect(['cloud_share', 'cloud_backup'])
            ->filter(fn (string $capability): bool => $this->boolean("entitlements.{$capability}.enabled"))
            ->mapWithKeys(fn (string $capability): array => [
                $capability => (int) data_get($data, "entitlements.{$capability}.quota"),
            ])
            ->all();
    }
}
