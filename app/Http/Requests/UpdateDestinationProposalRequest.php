<?php

namespace App\Http\Requests;

use App\Models\DestinationProposal;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDestinationProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, DestinationProposal::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'proposed_by' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'name' => 'sometimes|nullable|string',
            'lat' => 'sometimes|nullable|numeric',
            'lng' => 'sometimes|nullable|numeric',
            'pitch' => 'sometimes|nullable|string',
            'estimated_cost' => 'sometimes|nullable|integer',
        ];
    }
}
