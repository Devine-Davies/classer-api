<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates confirmation requests for an existing CloudShare.
 */
class CloudShareConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to confirm this share.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Only the owner of the share may confirm uploads
        $share = $this->route('cloudShareUID');
        return $this->user() !== null
            && $share !== null
            && $share->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     * No body payload required for confirmation.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
