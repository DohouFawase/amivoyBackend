<?php

namespace App\Http\Requests;

use App\Models\Notification;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Notification::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'category' => 'sometimes|nullable|string',
            'type' => 'sometimes|nullable|string',
            'title' => 'sometimes|nullable|string',
            'body' => 'sometimes|nullable|string',
            'data' => 'sometimes|nullable|array',
            'read_at' => 'sometimes|nullable|date',
            'sent_at' => 'sometimes|nullable|date',
        ];
    }
}
