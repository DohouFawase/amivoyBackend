<?php

namespace App\Http\Requests;

use App\Models\Plan;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Plan::class);
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string',
            'max_members' => 'required|integer',
            'photo_quota_mb' => 'sometimes|nullable|integer',
            'features' => 'sometimes|nullable|array',
            'price' => 'required|integer',
            'billing_period' => 'sometimes|nullable|string',
        ];
    }
}
