<?php

namespace App\Http\Requests;

use App\Models\Partner;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Partner::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'type' => 'required|string',
            'country' => 'required|string',
            'commission_rate' => 'sometimes|nullable|numeric',
            'contact_email' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
