<?php

namespace App\Http\Requests;

use App\Models\Recommendation;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Recommendation::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'score' => 'required|numeric',
            'reason' => 'sometimes|nullable|string',
        ];
    }
}
