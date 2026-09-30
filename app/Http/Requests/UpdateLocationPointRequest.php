<?php

namespace App\Http\Requests;

use App\Models\LocationPoint;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, LocationPoint::class);
    }

    public function rules(): array
    {
        return [
            'share_id' => ['sometimes', 'nullable', 'uuid', 'exists:location_shares,id'],
            'lat' => 'sometimes|nullable|numeric',
            'lng' => 'sometimes|nullable|numeric',
            'accuracy_m' => 'sometimes|nullable|numeric',
            'recorded_at' => 'sometimes|nullable|date',
        ];
    }
}
