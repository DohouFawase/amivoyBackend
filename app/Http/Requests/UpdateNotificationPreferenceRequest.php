<?php

namespace App\Http\Requests;

use App\Models\NotificationPreference;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, NotificationPreference::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'push_enabled' => 'sometimes|nullable|boolean',
            'email_enabled' => 'sometimes|nullable|boolean',
            'sms_enabled' => 'sometimes|nullable|boolean',
            'per_type_settings' => 'sometimes|nullable|array',
            'quiet_from' => 'sometimes|nullable|date_format:H:i:s',
            'quiet_to' => 'sometimes|nullable|date_format:H:i:s',
        ];
    }
}
