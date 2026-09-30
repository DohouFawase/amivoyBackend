<?php

namespace App\Http\Requests;

use App\Models\TripMember;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTripMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, TripMember::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'role' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
            'joined_at' => 'sometimes|nullable|date',
            'left_at' => 'sometimes|nullable|date',
        ];
    }
}
