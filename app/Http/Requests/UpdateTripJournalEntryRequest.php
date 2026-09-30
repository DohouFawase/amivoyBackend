<?php

namespace App\Http\Requests;

use App\Models\TripJournalEntry;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTripJournalEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $aliases = ['body' => 'content', 'day' => 'day_label'];
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
        return TripV1Access::authorize($this, TripJournalEntry::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string', 'max:20000'],
            'place_label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'happened_at' => ['sometimes', 'nullable', 'date'],
            'day_label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'emoji' => ['sometimes', 'nullable', 'string', 'max:24'],
        ];
    }
}
