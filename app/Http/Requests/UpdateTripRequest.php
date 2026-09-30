<?php

namespace App\Http\Requests;

use App\Models\Circle;
use App\Models\Trip;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $aliases = [
            'title' => 'name',
            'destination' => 'destination_label',
            'image' => 'cover_url',
            'color' => 'cover_color',
            'next' => 'next_label',
            'people' => 'estimated_members',
            'budget' => 'planned_budget',
            'circleId' => 'circle_id',
            'members' => 'member_names',
            'dates' => 'display_dates',
            'days' => 'duration_days',
            'isPrivate' => 'visibility',
        ];
        $normalized = [];
        foreach ($aliases as $source => $target) {
            if ($this->exists($source) && ! $this->exists($target)) {
                $value = $this->input($source);
                $normalized[$target] = $source === 'isPrivate' ? ($value ? 'private' : 'public') : $value;
            }
        }
        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        if ($this->filled('circle_id')) {
            return Circle::query()->accessibleTo($this->user())
                ->whereKey($this->input('circle_id'))->exists()
                && TripV1Access::authorize($this, Trip::class);
        }

        return TripV1Access::authorize($this, Trip::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string',
            'member_names' => ['sometimes', 'array'],
            'member_names.*' => ['string', 'max:120'],
            'display_dates' => ['sometimes', 'nullable', 'string', 'max:120'],
            'duration_days' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:366'],
            'circle_id' => ['sometimes', 'nullable', 'uuid', 'exists:circles,id'],
            'description' => 'sometimes|nullable|string',
            'cover_url' => 'sometimes|nullable|string|max:2048',
            'cover_color' => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}([0-9A-Fa-f]{2})?$/'],
            'next_label' => 'sometimes|nullable|string|max:180',
            'destination_label' => 'sometimes|nullable|string',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'currency' => 'sometimes|nullable|string',
            'planned_budget' => 'sometimes|nullable|integer',
            'estimated_members' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|string',
            'governance_rules' => 'sometimes|nullable|array',
            'visibility' => ['sometimes', 'string', 'in:private,public'],
        ];
    }
}
