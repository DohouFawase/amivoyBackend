<?php

namespace App\Http\Requests;

use App\Models\EmergencyAlertRecipient;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmergencyAlertRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, EmergencyAlertRecipient::class);
    }

    public function rules(): array
    {
        return [
            'alert_id' => ['sometimes', 'nullable', 'uuid', 'exists:emergency_alerts,id'],
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'delivered_at' => 'sometimes|nullable|date',
            'acknowledged_at' => 'sometimes|nullable|date',
        ];
    }
}
