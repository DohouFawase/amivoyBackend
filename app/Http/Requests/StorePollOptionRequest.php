<?php

namespace App\Http\Requests;

use App\Models\PollOption;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePollOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, PollOption::class);
    }

    public function rules(): array
    {
        return [
            'poll_id' => ['required', 'uuid', 'exists:polls,id'],
            'label' => 'required|string',
            'payload' => 'sometimes|nullable|array',
            'position' => 'sometimes|nullable|integer',
        ];
    }
}
