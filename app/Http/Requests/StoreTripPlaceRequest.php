<?php

namespace App\Http\Requests;

use App\Models\TripPlace;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreTripPlaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, TripPlace::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'place_id' => ['required_without:city', 'nullable', 'uuid', 'exists:places,id'],
            'added_by' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'likes_count' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'note' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string|max:120',
            'country' => 'sometimes|nullable|string|max:120',
            'position' => 'sometimes|nullable|integer|min:0',
            'stay_start' => 'sometimes|nullable|date',
            'stay_end' => 'sometimes|nullable|date|after_or_equal:stay_start',
            'lodging_name' => 'sometimes|nullable|string|max:180',
            'lodging_price' => 'sometimes|nullable|integer|min:0',
            'lodging_currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}
