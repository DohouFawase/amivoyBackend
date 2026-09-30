<?php

namespace App\Http\Requests;

use App\Models\ExchangeRate;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, ExchangeRate::class);
    }

    public function rules(): array
    {
        return [
            'base' => 'required|string',
            'quote' => 'required|string',
            'rate' => 'required|numeric',
            'source' => 'sometimes|nullable|string',
            'fetched_at' => 'sometimes|nullable|date',
        ];
    }
}
