<?php

namespace App\Http\Requests;

use App\Models\ExclusionRequest;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreExclusionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ExclusionRequest::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'target_member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'requested_by' => ['required', 'uuid', 'exists:trip_members,id'],
            'poll_id' => ['sometimes', 'nullable', 'uuid', 'exists:polls,id'],
            'reason' => 'required|string',
            'status' => 'sometimes|nullable|string',
            'decided_at' => 'sometimes|nullable|date',
        ];
    }
}
