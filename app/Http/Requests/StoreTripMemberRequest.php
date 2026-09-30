<?php

namespace App\Http\Requests;

use App\Models\TripMember;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreTripMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, TripMember::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'role' => 'required|string',
            'status' => 'required|string',
            'joined_at' => 'sometimes|nullable|date',
            'left_at' => 'sometimes|nullable|date',
        ];
    }
}
