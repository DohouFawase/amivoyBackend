<?php

namespace App\Http\Requests;

use App\Models\EmergencyAlert;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmergencyAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, EmergencyAlert::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'lat' => 'sometimes|nullable|numeric',
            'lng' => 'sometimes|nullable|numeric',
            'position_is_last_known' => 'sometimes|nullable|boolean',
            'status' => 'sometimes|nullable|string',
            'triggered_at' => 'sometimes|nullable|date',
            'resolved_at' => 'sometimes|nullable|date',
        ];
    }
}
