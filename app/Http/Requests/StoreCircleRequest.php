<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCircleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'members' => ['sometimes', 'array'],
            'members.*' => ['string', 'max:120'],
            'member_user_ids' => ['sometimes', 'array'],
            'member_user_ids.*' => ['uuid', 'exists:users,id'],
        ];
    }
}
