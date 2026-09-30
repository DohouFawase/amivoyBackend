<?php

namespace App\Http\Requests;

use App\Models\Circle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $aliases = [
            'locationType' => 'location_type',
            'date' => 'date_label',
            'time' => 'time_label',
            'budgetTarget' => 'budget_target',
            'circleId' => 'circle_id',
        ];
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
        if ($this->user() === null) {
            return false;
        }

        if (! $this->filled('circle_id')) {
            return true;
        }

        return Circle::query()
            ->whereKey($this->input('circle_id'))
            ->where(function ($query): void {
                $query->where('creator_id', $this->user()->getKey())
                    ->orWhereJsonContains('member_user_ids', (string) $this->user()->getKey());
            })
            ->exists();
    }

    public function rules(): array
    {
        return [
            'circle_id' => ['sometimes', 'nullable', 'uuid', 'exists:circles,id'],
            'title' => ['required', 'string', 'max:180'],
            'place' => ['required', 'string', 'max:500'],
            'location_type' => ['required', Rule::in(['public', 'private'])],
            'category' => ['required', 'string', 'max:80'],
            'date_label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'time_label' => ['sometimes', 'nullable', 'string', 'max:40'],
            'note' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'activity' => ['sometimes', 'nullable', 'string', 'max:180'],
            'budget_target' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'guests' => ['sometimes', 'array'],
            'guests.*' => ['string', 'max:120'],
            'participant_user_ids' => ['sometimes', 'array'],
            'participant_user_ids.*' => ['uuid', 'exists:users,id'],
        ];
    }
}
