<?php

namespace App\Http\Requests;

use App\Models\Report;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Report::class);
    }

    public function rules(): array
    {
        return [
            'reporter_id' => ['required', 'uuid', 'exists:users,id'],
            'target_type' => 'required|string',
            'target_id' => 'required|uuid',
            'reason' => 'required|string',
            'details' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
