<?php

namespace App\Http\Requests;

use App\Models\LocationShare;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, LocationShare::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'duration_mode' => 'sometimes|nullable|string',
            'started_at' => 'sometimes|nullable|date',
            'expires_at' => 'sometimes|nullable|date',
            'stopped_at' => 'sometimes|nullable|date',
        ];
    }
}
