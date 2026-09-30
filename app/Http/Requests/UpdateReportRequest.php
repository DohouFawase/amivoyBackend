<?php

namespace App\Http\Requests;

use App\Models\Report;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Report::class);
    }

    public function rules(): array
    {
        return [
            'reporter_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'target_type' => 'sometimes|nullable|string',
            'target_id' => 'sometimes|nullable|uuid',
            'reason' => 'sometimes|nullable|string',
            'details' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
