<?php

namespace App\Http\Requests;

use App\Models\Photo;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Photo::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:active,hidden,reported'],
            'taken_at' => ['sometimes', 'nullable', 'date'],
            'caption' => ['sometimes', 'nullable', 'string', 'max:500'],
            'shared_to_story' => ['sometimes', 'boolean'],
        ];
    }
}
