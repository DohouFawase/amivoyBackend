<?php

namespace App\Http\Requests;

use App\Models\UserSession;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, UserSession::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'device_name' => 'sometimes|nullable|string',
            'ip_address' => 'sometimes|nullable|string',
            'expires_at' => 'sometimes|nullable|date',
            'revoked_at' => 'sometimes|nullable|date',
        ];
    }
}
