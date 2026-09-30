<?php

namespace App\Http\Requests;

use App\Models\OfflineSyncQueue;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfflineSyncQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, OfflineSyncQueue::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'trip_id' => ['sometimes', 'nullable', 'uuid', 'exists:trips,id'],
            'operation' => 'sometimes|nullable|string',
            'payload' => 'sometimes|nullable|array',
            'client_op_id' => ['sometimes', 'nullable', 'string', Rule::unique('offline_sync_queue', 'client_op_id')->ignore($this->route('id'))],
            'status' => 'sometimes|nullable|string',
        ];
    }
}
