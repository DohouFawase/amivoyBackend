<?php

namespace App\Http\Requests;

use App\Models\DeviceToken;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, DeviceToken::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'platform' => 'required|string',
            'fcm_apns_token' => 'required|string|unique:device_tokens,fcm_apns_token',
            'last_seen_at' => 'sometimes|nullable|date',
        ];
    }
}
