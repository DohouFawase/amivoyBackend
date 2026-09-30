<?php

namespace App\Http\Requests;

use App\Models\OfflineSyncQueue;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfflineSyncQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, OfflineSyncQueue::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'operation' => 'required|string',
            'payload' => 'required|array',
            'client_op_id' => 'required|string|unique:offline_sync_queue,client_op_id',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
