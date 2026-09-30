<?php

namespace App\Http\Requests;

use App\Models\EmergencyAlertRecipient;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmergencyAlertRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, EmergencyAlertRecipient::class);
    }

    public function rules(): array
    {
        return [
            'alert_id' => ['required', 'uuid', 'exists:emergency_alerts,id'],
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'delivered_at' => 'sometimes|nullable|date',
            'acknowledged_at' => 'sometimes|nullable|date',
        ];
    }
}
