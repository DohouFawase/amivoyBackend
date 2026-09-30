<?php

namespace App\Http\Requests;

use App\Models\EmergencyAlert;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, EmergencyAlert::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'position_is_last_known' => 'sometimes|nullable|boolean',
            'status' => 'required|string',
            'triggered_at' => 'sometimes|nullable|date',
            'resolved_at' => 'sometimes|nullable|date',
        ];
    }
}
