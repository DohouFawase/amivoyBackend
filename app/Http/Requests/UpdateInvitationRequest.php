<?php

namespace App\Http\Requests;

use App\Models\Invitation;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Invitation::class);
    }

    public function rules(): array
    {
        return [
            'channel' => ['sometimes', 'string', 'in:whatsapp,sms,email,link'],
            'target' => ['sometimes', 'nullable', 'string', 'max:320'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
