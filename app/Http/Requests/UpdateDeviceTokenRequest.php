<?php

namespace App\Http\Requests;

use App\Models\DeviceToken;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, DeviceToken::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'platform' => 'sometimes|nullable|string',
            'fcm_apns_token' => ['sometimes', 'nullable', 'string', Rule::unique('device_tokens', 'fcm_apns_token')->ignore($this->route('id'))],
            'last_seen_at' => 'sometimes|nullable|date',
        ];
    }
}
