<?php

namespace App\Http\Requests;

use App\Models\Reminder;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Reminder::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'outing_id' => ['sometimes', 'nullable', 'uuid', 'exists:outings,id'],
            'type' => 'sometimes|nullable|string',
            'target_type' => 'sometimes|nullable|string',
            'target_id' => 'sometimes|nullable|uuid',
            'fire_at' => 'sometimes|nullable|date',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
