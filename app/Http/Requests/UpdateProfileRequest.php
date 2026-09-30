<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($this->user()->getKey())],
            'language' => ['sometimes', 'required', 'string', Rule::in(['fr', 'en'])],
            'country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['string', 'max:80'],
        ];
    }
}
