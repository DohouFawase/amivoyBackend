<?php

namespace App\Http\Requests;

use App\Models\ModerationAction;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateModerationActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ModerationAction::class);
    }

    public function rules(): array
    {
        return [
            'report_id' => ['sometimes', 'nullable', 'uuid', 'exists:reports,id'],
            'moderator_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'action' => 'sometimes|nullable|string',
            'note' => 'sometimes|nullable|string',
        ];
    }
}
