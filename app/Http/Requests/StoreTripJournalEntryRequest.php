<?php

namespace App\Http\Requests;

use App\Models\TripJournalEntry;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StoreTripJournalEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'content' => $this->input('content', $this->input('body')),
            'day_label' => $this->input('day_label', $this->input('day')),
        ]);
    }

    public function authorize(): bool
    {
        return TripV1Access::authorize($this, TripJournalEntry::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'place_label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'happened_at' => ['sometimes', 'nullable', 'date'],
            'day_label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'emoji' => ['sometimes', 'nullable', 'string', 'max:24'],
        ];
    }
}
