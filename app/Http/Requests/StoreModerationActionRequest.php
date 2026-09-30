<?php

namespace App\Http\Requests;

use App\Models\ModerationAction;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreModerationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ModerationAction::class);
    }

    public function rules(): array
    {
        return [
            'report_id' => ['required', 'uuid', 'exists:reports,id'],
            'moderator_id' => ['required', 'uuid', 'exists:users,id'],
            'action' => 'required|string',
            'note' => 'sometimes|nullable|string',
        ];
    }
}
