<?php

namespace App\Http\Requests;

use App\Models\ActivityAttendee;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityAttendeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ActivityAttendee::class);
    }

    public function rules(): array
    {
        return [
            'activity_id' => ['sometimes', 'nullable', 'uuid', 'exists:activities,id'],
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'rsvp' => 'sometimes|nullable|string',
        ];
    }
}
