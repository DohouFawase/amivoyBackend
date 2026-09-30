<?php

namespace App\Http\Requests;

use App\Models\Circle;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCircleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Circle::query()
            ->whereKey($this->route('id'))
            ->where('creator_id', $this->user()?->getKey())
            ->exists();
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'members' => ['sometimes', 'array'],
            'members.*' => ['string', 'max:120'],
            'member_user_ids' => ['sometimes', 'array'],
            'member_user_ids.*' => ['uuid', 'exists:users,id'],
        ];
    }
}
