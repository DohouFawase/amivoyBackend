<?php

namespace App\Http\Requests;

use App\Models\Budget;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Budget::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'total_planned' => 'required|integer',
            'currency' => 'required|string',
            'version' => 'sometimes|nullable|integer',
        ];
    }
}
