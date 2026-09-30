<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Service::class);
    }

    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'place_id' => ['sometimes', 'nullable', 'uuid', 'exists:places,id'],
            'title' => 'required|string',
            'price' => 'required|integer',
            'currency' => 'required|string',
            'availability' => 'sometimes|nullable|array',
            'category' => 'sometimes|nullable|string|max:80',
            'description' => 'sometimes|nullable|string|max:1000',
            'icon' => 'sometimes|nullable|string|max:60',
            'rating' => 'sometimes|nullable|integer|between:0,5',
        ];
    }
}
