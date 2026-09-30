<?php

namespace App\Http\Requests;

use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class NotifyTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $tripId = (string) $this->route('trip');

        return $user !== null && TripV1Access::canManageTrip($user, $tripId);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'category' => ['sometimes', 'string', 'in:normal,important,critical'],
            'data' => ['sometimes', 'array'],
        ];
    }
}
