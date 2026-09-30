<?php

namespace App\Http\Requests;

use App\Models\PackingItem;
use App\Support\TripV1Access;
use Illuminate\Foundation\Http\FormRequest;

class StorePackingItemRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $aliases = ['label' => 'title', 'done' => 'is_packed'];
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
        return TripV1Access::authorize($this, PackingItem::class);
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'uuid', 'exists:trips,id'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:999'],
            'is_packed' => ['sometimes', 'boolean'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
