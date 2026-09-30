<?php

namespace App\Http\Requests;

use App\Models\MeetingPoint;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, MeetingPoint::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'created_by' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'activity_id' => ['sometimes', 'nullable', 'uuid', 'exists:activities,id'],
            'name' => 'sometimes|nullable|string',
            'lat' => 'sometimes|nullable|numeric',
            'lng' => 'sometimes|nullable|numeric',
            'meet_at' => 'sometimes|nullable|date',
            'instructions' => 'sometimes|nullable|string',
        ];
    }
}
