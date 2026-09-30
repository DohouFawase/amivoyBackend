<?php

namespace App\Http\Requests;

use App\Models\ExchangeRate;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ExchangeRate::class);
    }

    public function rules(): array
    {
        return [
            'base' => 'sometimes|nullable|string',
            'quote' => 'sometimes|nullable|string',
            'rate' => 'sometimes|nullable|numeric',
            'source' => 'sometimes|nullable|string',
            'fetched_at' => 'sometimes|nullable|date',
        ];
    }
}
