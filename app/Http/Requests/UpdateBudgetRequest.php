<?php

namespace App\Http\Requests;

use App\Models\Budget;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Budget::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'total_planned' => 'sometimes|nullable|integer',
            'currency' => 'sometimes|nullable|string',
            'version' => 'sometimes|nullable|integer',
        ];
    }
}
