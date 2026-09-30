<?php

namespace App\Http\Requests;

use App\Models\Place;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Place::class);
    }

    public function rules(): array
    {
        return [
            'provider' => 'sometimes|nullable|string',
            'provider_place_id' => 'sometimes|nullable|string',
            'name' => 'required|string',
            'category' => 'sometimes|nullable|string',
            'lat' => 'sometimes|nullable|numeric',
            'lng' => 'sometimes|nullable|numeric',
            'address' => 'sometimes|nullable|string',
            'opening_hours' => 'sometimes|nullable|array',
            'cached_data' => 'sometimes|nullable|array',
            'cached_at' => 'sometimes|nullable|date',
            'country' => 'sometimes|nullable|string|max:120',
            'region' => 'sometimes|nullable|string|max:120',
            'place_type' => 'sometimes|nullable|string|max:60',
        ];
    }
}
