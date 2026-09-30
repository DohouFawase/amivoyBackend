<?php

namespace App\Http\Requests;

use App\Models\Invitation;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Invitation::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required_without_all:outing_id,circle_id', 'prohibited_with:outing_id,circle_id', 'nullable', 'uuid', 'exists:trips,id'],
            'outing_id' => ['required_without_all:trip_id,circle_id', 'prohibited_with:trip_id,circle_id', 'nullable', 'uuid', 'exists:outings,id'],
            'circle_id' => ['required_without_all:trip_id,outing_id', 'prohibited_with:trip_id,outing_id', 'nullable', 'uuid', 'exists:circles,id'],
            'invited_by' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'channel' => ['required', 'string', 'in:whatsapp,sms,email,link'],
            'target' => 'sometimes|nullable|string',
            'max_uses' => 'sometimes|nullable|integer',
            'use_count' => 'sometimes|nullable|integer',
            'recipient_user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'expires_at' => 'sometimes|nullable|date',
        ];
    }
}
