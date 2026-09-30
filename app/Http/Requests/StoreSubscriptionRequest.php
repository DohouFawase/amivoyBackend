<?php

namespace App\Http\Requests;

use App\Models\Subscription;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Subscription::class);
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'uuid', 'exists:plans,id'],
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'status' => 'required|string',
            'current_period_end' => 'sometimes|nullable|date',
        ];
    }
}
