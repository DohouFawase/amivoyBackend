<?php

namespace App\Http\Requests;

use App\Models\ExclusionRequest;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExclusionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ExclusionRequest::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'target_member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'requested_by' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'poll_id' => ['sometimes', 'nullable', 'uuid', 'exists:polls,id'],
            'reason' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
            'decided_at' => 'sometimes|nullable|date',
        ];
    }
}
