<?php

namespace App\Http\Requests;

use App\Models\PhotoComment;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, PhotoComment::class);
    }

    public function rules(): array
    {
        return [
            'photo_id' => ['sometimes', 'nullable', 'uuid', 'exists:photos,id'],
            'member_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'content' => 'sometimes|nullable|string',
        ];
    }
}
