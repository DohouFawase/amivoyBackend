<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripRoutePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'origin_lat' => ['sometimes', 'required_with:origin_lng', 'numeric', 'between:-90,90'],
            'origin_lng' => ['sometimes', 'required_with:origin_lat', 'numeric', 'between:-180,180'],
        ];
    }
}
