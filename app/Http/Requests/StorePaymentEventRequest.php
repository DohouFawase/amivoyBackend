<?php

namespace App\Http\Requests;

use App\Models\PaymentEvent;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, PaymentEvent::class);
    }

    public function rules(): array
    {
        return [
            'payment_id' => ['sometimes', 'nullable', 'uuid', 'exists:payments,id'],
            'event_type' => 'sometimes|nullable|string',
            'received_at' => 'sometimes|nullable|date',
        ];
    }
}
