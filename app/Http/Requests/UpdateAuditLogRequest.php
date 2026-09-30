<?php

namespace App\Http\Requests;

use App\Models\AuditLog;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, AuditLog::class);
    }

    public function rules(): array
    {
        return [
            'actor_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'action' => 'sometimes|nullable|string',
            'entity_type' => 'sometimes|nullable|string',
            'entity_id' => 'sometimes|nullable|uuid',
            'before' => 'sometimes|nullable|array',
            'after' => 'sometimes|nullable|array',
            'ip_address' => 'sometimes|nullable|string',
        ];
    }
}
