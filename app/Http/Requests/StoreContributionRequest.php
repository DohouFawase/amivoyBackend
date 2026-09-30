<?php

namespace App\Http\Requests;

use App\Models\Contribution;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Contribution::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'expected_amount' => 'required|integer',
            'paid_amount' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'due_date' => 'sometimes|nullable|date',
        ];
    }
}
