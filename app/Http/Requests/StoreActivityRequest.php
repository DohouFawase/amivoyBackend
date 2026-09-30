<?php

namespace App\Http\Requests;

use App\Models\Activity;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $aliases = ['tripId' => 'trip_id', 'type' => 'category', 'emoji' => 'icon', 'location' => 'location_label', 'time' => 'time_label'];
        $normalized = [];
        foreach ($aliases as $source => $target) {
            if ($this->exists($source) && ! $this->exists($target)) {
                $normalized[$target] = $this->input($source);
            }
        }
        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        return TripV1Access::authorize($this, Activity::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'trip_place_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_places,id'],
            'responsible_id' => ['sometimes', 'nullable', 'uuid', 'exists:trip_members,id'],
            'title' => 'required|string',
            'description' => 'sometimes|nullable|string',
            'starts_at' => 'sometimes|nullable|date',
            'duration_min' => 'sometimes|nullable|integer',
            'estimated_cost' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'notes' => 'sometimes|nullable|string',
            'category' => 'sometimes|nullable|string|max:80',
            'icon' => 'sometimes|nullable|string|max:60',
            'location_label' => ['sometimes', 'nullable', 'string', 'max:500'],
            'time_label' => ['sometimes', 'nullable', 'string', 'max:40'],
        ];
    }
}
