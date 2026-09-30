<?php

namespace App\Http\Requests;

use App\Models\PollOption;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePollOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, PollOption::class);
    }

    public function rules(): array
    {
        return [
            'poll_id' => ['sometimes', 'nullable', 'uuid', 'exists:polls,id'],
            'label' => 'sometimes|nullable|string',
            'payload' => 'sometimes|nullable|array',
            'position' => 'sometimes|nullable|integer',
        ];
    }
}
