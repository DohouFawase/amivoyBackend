<?php

namespace App\Http\Requests;

use App\Models\Payment;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Payment::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'contribution_id' => ['sometimes', 'nullable', 'uuid', 'exists:contributions,id'],
            'settlement_id' => ['sometimes', 'nullable', 'uuid', 'exists:settlements,id'],
            'provider' => 'sometimes|nullable|string',
            'provider_ref' => 'sometimes|nullable|string|unique:payments,provider_ref',
            'amount' => 'sometimes|nullable|integer',
            'currency' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
            'failure_reason' => 'sometimes|nullable|string',
            'idempotency_key' => 'sometimes|nullable|string|unique:payments,idempotency_key',
        ];
    }
}
