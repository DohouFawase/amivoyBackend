<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Booking::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'service_id' => ['required', 'uuid', 'exists:services,id'],
            'booked_by' => ['required', 'uuid', 'exists:trip_members,id'],
            'activity_id' => ['sometimes', 'nullable', 'uuid', 'exists:activities,id'],
            'quantity' => 'required|integer',
            'total_amount' => 'required|integer',
            'status' => 'sometimes|nullable|string',
            'partner_ref' => 'sometimes|nullable|string',
        ];
    }
}
