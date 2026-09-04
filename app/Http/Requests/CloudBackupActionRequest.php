<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloudBackupActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $backup = $this->route('cloudBackupUID');

        return $this->user() !== null
            && $backup !== null
            && $backup->user_id === $this->user()->uid;
    }

    public function rules(): array
    {
        return [];
    }
}
