<?php

namespace App\Http\Requests;

use App\Models\LocationPoint;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, LocationPoint::class);
    }

    public function rules(): array
    {
        return [
            'share_id' => ['required', 'uuid', 'exists:location_shares,id'],
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'accuracy_m' => 'sometimes|nullable|numeric',
            'recorded_at' => 'required|date',
        ];
    }
}
