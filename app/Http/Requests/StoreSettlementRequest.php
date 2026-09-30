<?php

namespace App\Http\Requests;

use App\Models\Settlement;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreSettlementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Settlement::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'from_member' => ['required', 'uuid', 'exists:trip_members,id'],
            'to_member' => ['required', 'uuid', 'exists:trip_members,id'],
            'amount' => 'required|integer',
            'status' => 'sometimes|nullable|string',
            'confirmed_at' => 'sometimes|nullable|date',
        ];
    }
}
