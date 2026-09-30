<?php

namespace App\Http\Requests;

use App\Models\Reaction;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Reaction::class);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'uuid', 'exists:trip_members,id'],
            'target_type' => 'required|string',
            'target_id' => 'required|uuid',
            'emoji' => 'required|string',
        ];
    }
}
