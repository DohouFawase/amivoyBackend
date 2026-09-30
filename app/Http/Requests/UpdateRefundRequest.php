<?php

namespace App\Http\Requests;

use App\Models\Refund;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Refund::class);
    }

    public function rules(): array
    {
        return [
            'payment_id' => ['sometimes', 'nullable', 'uuid', 'exists:payments,id'],
            'amount' => 'sometimes|nullable|integer',
            'reason' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
            'provider_ref' => 'sometimes|nullable|string',
        ];
    }
}
