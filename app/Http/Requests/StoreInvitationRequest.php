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
            'trip_id' => ['required_without_all:outing_id,circle_id', 'nullable', 'uuid', 'exists:trips,id', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($this->filled('outing_id') || $this->filled('circle_id')) {
                    $fail('Une invitation ne peut cibler qu’un seul voyage, cercle ou événement.');
                }
            }],
            'outing_id' => ['required_without_all:trip_id,circle_id', 'nullable', 'uuid', 'exists:outings,id', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($this->filled('trip_id') || $this->filled('circle_id')) {
                    $fail('Une invitation ne peut cibler qu’un seul voyage, cercle ou événement.');
                }
            }],
            'circle_id' => ['required_without_all:trip_id,outing_id', 'nullable', 'uuid', 'exists:circles,id', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($this->filled('trip_id') || $this->filled('outing_id')) {
                    $fail('Une invitation ne peut cibler qu’un seul voyage, cercle ou événement.');
                }
            }],
            'invited_by' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'channel' => ['required', 'string', 'in:whatsapp,sms,email,link'],
            'target' => ['required_if:channel,email', 'nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($this->input('channel') === 'email' && filled($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('Saisis une adresse e-mail valide pour envoyer l’invitation.');
                }
            }],
            'max_uses' => 'sometimes|nullable|integer',
            'use_count' => 'sometimes|nullable|integer',
            'recipient_user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'expires_at' => 'sometimes|nullable|date',
        ];
    }
}
