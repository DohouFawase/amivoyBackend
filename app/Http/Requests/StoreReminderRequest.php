<?php

namespace App\Http\Requests;

use App\Models\Reminder;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Reminder::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required_without:outing_id', 'nullable', 'uuid', 'exists:trips,id'],
            'outing_id' => ['required_without:trip_id', 'nullable', 'uuid', 'exists:outings,id'],
            'type' => 'required|string',
            'target_type' => 'sometimes|nullable|string',
            'target_id' => 'sometimes|nullable|uuid',
            'fire_at' => 'required|date',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
