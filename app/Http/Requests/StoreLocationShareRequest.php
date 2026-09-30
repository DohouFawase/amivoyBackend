<?php

namespace App\Http\Requests;

use App\Models\LocationShare;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationShareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, LocationShare::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'duration_mode' => 'sometimes|nullable|string',
            'started_at' => 'sometimes|nullable|date',
            'expires_at' => 'required|date',
            'stopped_at' => 'sometimes|nullable|date',
        ];
    }
}
