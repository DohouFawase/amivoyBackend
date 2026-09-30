<?php

namespace App\Http\Requests;

use App\Models\Recommendation;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Recommendation::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'service_id' => ['sometimes', 'nullable', 'uuid', 'exists:services,id'],
            'score' => 'sometimes|nullable|numeric',
            'reason' => 'sometimes|nullable|string',
        ];
    }
}
