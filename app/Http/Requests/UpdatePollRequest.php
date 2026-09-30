<?php

namespace App\Http\Requests;

use App\Models\Poll;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Poll::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'created_by' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'type' => 'sometimes|nullable|string',
            'title' => 'sometimes|nullable|string',
            'multiple_choice' => 'sometimes|nullable|boolean',
            'anonymous' => 'sometimes|nullable|boolean',
            'quorum_percent' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'closes_at' => 'sometimes|nullable|date',
            'winning_option_id' => ['sometimes', 'nullable', 'uuid', 'exists:poll_options,id'],
        ];
    }
}
