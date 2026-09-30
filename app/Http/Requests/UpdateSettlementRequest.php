<?php

namespace App\Http\Requests;

use App\Models\Settlement;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Settlement::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'from_member' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'to_member' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'amount' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'confirmed_at' => 'sometimes|nullable|date',
        ];
    }
}
