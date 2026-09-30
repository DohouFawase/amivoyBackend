<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['outing', 'trip', 'circle'])],
            'group_id' => ['required', 'string', 'max:80'],
            'group_name' => ['required', 'string', 'max:180'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'actor' => ['sometimes', 'nullable', 'string', 'max:120'],
            'time_label' => ['sometimes', 'nullable', 'string', 'max:80'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:60'],
            'href' => ['sometimes', 'nullable', 'string', 'max:300'],
        ];
    }
}
