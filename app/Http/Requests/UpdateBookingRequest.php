<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Booking::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'service_id' => ['sometimes', 'nullable', 'uuid', 'exists:services,id'],
            'booked_by' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'activity_id' => ['sometimes', 'nullable', 'uuid', 'exists:activities,id'],
            'quantity' => 'sometimes|nullable|integer',
            'total_amount' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'partner_ref' => 'sometimes|nullable|string',
        ];
    }
}
