<?php

namespace App\Http\Requests;

use App\Models\PollAnswer;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePollAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, PollAnswer::class);
    }

    public function rules(): array
    {
        return [
            'option_id' => ['required', 'uuid', 'exists:poll_options,id'],
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'voted_at' => 'sometimes|nullable|date',
            'changed_at' => 'sometimes|nullable|date',
        ];
    }
}
