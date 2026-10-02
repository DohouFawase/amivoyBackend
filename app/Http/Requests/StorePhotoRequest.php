<?php

namespace App\Http\Requests;

use App\Models\Photo;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Photo::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required_without:outing_id', 'nullable', 'uuid', 'exists:trips,id'],
            'outing_id' => ['required_without:trip_id', 'nullable', 'uuid', 'exists:outings,id'],
            'uploaded_by' => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],
            'storage_key' => ['required_without:image', 'nullable', 'string'],
            'image' => ['required_without:storage_key', 'image', 'max:10240'],
            'thumbnail_key' => 'sometimes|nullable|string',
            'mime_type' => ['required_without:image', 'nullable', 'string'],
            'size_bytes' => 'sometimes|nullable|integer',
            'width' => 'sometimes|nullable|integer',
            'height' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'taken_at' => 'sometimes|nullable|date',
            'caption' => 'sometimes|nullable|string|max:500',
            'shared_to_story' => 'sometimes|boolean',
        ];
    }
}
