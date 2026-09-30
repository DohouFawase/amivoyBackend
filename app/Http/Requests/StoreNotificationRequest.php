<?php

namespace App\Http\Requests;

use App\Models\Notification;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Notification::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'category' => 'sometimes|nullable|string',
            'type' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'sometimes|nullable|array',
            'read_at' => 'sometimes|nullable|date',
            'sent_at' => 'sometimes|nullable|date',
        ];
    }
}
