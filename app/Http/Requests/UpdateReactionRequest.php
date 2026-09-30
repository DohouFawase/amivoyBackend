<?php

namespace App\Http\Requests;

use App\Models\Reaction;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Reaction::class);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'target_type' => 'sometimes|nullable|string',
            'target_id' => 'sometimes|nullable|uuid',
            'emoji' => 'sometimes|nullable|string',
        ];
    }
}
